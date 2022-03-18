<?php
require_once('./connection.php');

if(isset($_POST['ownerEmail'])) {

    $destination_path = getcwd().DIRECTORY_SEPARATOR;

    $file_name_arr = explode('.', $_FILES['uploadFile']['name']);
    $file_ext = end($file_name_arr);

    $file_name = substr(hash('sha256', basename($_FILES['uploadFile']['name'])), 0, 15);

    $target_path = $destination_path . 'uploads/' . $file_name;

    $file_contents = file_get_contents($_FILES['uploadFile']['tmp_name']);


    $passcode = hash('sha256', $_POST['password']);
    $cipher = 'aes-128-gcm';
    $ivlen = openssl_cipher_iv_length($cipher);
    $iv = openssl_random_pseudo_bytes($ivlen);

    $cipher_text = openssl_encrypt($file_contents, $cipher, $passcode, $options=0, $iv, $tag);

    $fp = fopen($target_path, 'w');
    fwrite($fp, $cipher_text);
    fclose($fp);

    chmod($target_path, 0664); // Ensure file cannot execute.

    $sender_email = filter_var($_POST['ownerEmail'], FILTER_SANITIZE_EMAIL);
    $receiver_email = filter_var($_POST['receiverEmail'], FILTER_SANITIZE_EMAIL);
    $date = new DateTime('now', new DateTimeZone('UTC'));
    $created_at = $date->format('Y-m-j G:i:s');
    $file_path = $target_path;


    if(!isset($sql_hostname) or !isset($sql_username) or !isset($sql_password) or !isset($sql_database)) { die; }
    $mysqli = new mysqli($sql_hostname, $sql_username, $sql_password, $sql_database);

    $stmt = $mysqli->prepare(
        "INSERT INTO files(passcode, iv, filepath, sender_email, receiver_email, created_at, tag, file_ext)
            VALUES(?, ?, ?, ?, ?, ?, ?, ?)
    ");


    $stmt->bind_param('ssssssss', $passcode, $iv, $file_path, $sender_email, $receiver_email, $created_at, $tag, $file_ext);

    $stmt->execute();
}




?>




<form action="http://localhost/encryption/index.php" method="post" enctype="multipart/form-data">
    <fieldset>
        <legend>Emails</legend>
        <label for="ownerEmail">Your Email:</label>
        <input type="email" id="ownerEmail" name="ownerEmail" required />

        <label for="receiverEmail">Their Email:</label>
        <input type="email" id="receiverEmail" name="receiverEmail" required />
    </fieldset>
    <fieldset>
        <legend>Password</legend>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required />
    </fieldset>
    <fieldset>
        <legend>File</legend>
        <label for="uploadFile">File:</label>
        <input type="file" id="uploadFile" name="uploadFile">

        <input type="submit" value="Submit" />
    </fieldset>
</form>

<?php
    if(isset($file_name))
    {
        echo "<p>Give this code to the reciever to access their file.<strong>$file_name</strong></p>";
    }
?>

<h1>Retrieve</h1>

<form action="http://localhost/encryption/decryption.php" method="post">
    <fieldset>
        <legend>Emails</legend>
        <label for="yourEmail">Your Email</label>
        <input type="email" name="yourEmail" id="yourEmail" required/>
    </fieldset>
    <fieldset>
        <legend>Passcodes</legend>
        <label for="retrievePassword">Enter the pass phrase</label>
        <input type="password" id="retrievePassword" name="password" required/>
        <label for="uniqueCode">Unique Code:</label>
        <input type="password" id="uniqueCode" name="uniqueCode" />
        <input type="submit" value="Retrieve" />
    </fieldset>
</form>