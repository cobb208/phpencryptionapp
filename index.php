<?php
namespace Encryption\Index;
require_once('template/header.php');
require_once('connection.php');
require_once('validation.php');

use DateTime;
use DateTimeZone;
use Encryption\Validation\ValidationRules;
use Exception;

$error_list = array();

if($_SERVER['REQUEST_METHOD'] === 'POST') {

    if(!isset($mysqli)) {
        http_response_code(500);
        exit();
    }

    $sender_email = '';
    $receiver_email = '';

    // need to validate all input
    // owner email, receiver email, password, and file
    if(validate_file_upload_input($error_list, $sender_email, $receiver_email)) {

        $destination_path = getcwd().DIRECTORY_SEPARATOR;
        $file_name_arr = explode('.', $_FILES['uploadFile']['name']);
        $file_ext = end($file_name_arr);
        $file_name = substr(hash('sha256', basename($_FILES['uploadFile']['name'])), 0, 15);
        $target_path = $destination_path . 'uploads/' . $file_name;
        $file_contents = file_get_contents($_FILES['uploadFile']['tmp_name']);


        $passcode = $_POST['password'];
        $cipher = 'aes-128-gcm';
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $cipher_text = openssl_encrypt($file_contents, $cipher, $passcode, 0, $iv, $tag);

        $fp = fopen($target_path, 'w');
        fwrite($fp, $cipher_text);
        fclose($fp);

        chmod($target_path, 0664);

        try {
            $date = new DateTime('now', new DateTimeZone('UTC'));
        } catch (Exception $e)
        {
            http_response_code(500);
            exit();
        }

        $created_at = $date->format('Y-m-j G:i:s');
        $file_path = $target_path;

        $passphrase = uniqid();

        $stmt = $mysqli->prepare(
            "INSERT INTO files(iv, filepath, sender_email, receiver_email, created_at, tag, file_ext, passphrase)
            VALUES(?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param('ssssssss', $iv, $file_path, $sender_email, $receiver_email, $created_at, $tag, $file_ext, $passphrase);

        $successful_creation = false;

        if($stmt->execute())
        {
            $successful_creation = true;
        }

    }
    // end validation

}

?>




<form action="http://localhost/encryption/index.php" method="post" enctype="multipart/form-data">
    <?php ValidationRules::generate_csrf_form_token(); ?>
    <fieldset>
        <legend>Emails</legend>
        <label for="ownerEmail">Your Email:</label>
        <input type="email" id="ownerEmail" name="ownerEmail" required />
        <?php
            if(in_array('ownerEmail', $error_list)) {
                echo '<p>Error in the provided email address</p>';
            }
        ?>
        <label for="receiverEmail">Their Email:</label>
        <input type="email" id="receiverEmail" name="receiverEmail" required />
        <?php
            if(in_array('receiverEmail', $error_list)) {
                echo '<p>Error in the provided email address</p>';
            }
        ?>
    </fieldset>
    <fieldset>
        <legend>Password</legend>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required />
        <?php
        if(in_array('password', $error_list)) {
            echo '<p>Error in the provided password</p>';
        }
        ?>
    </fieldset>
    <fieldset>
        <legend>File</legend>
        <label for="uploadFile">File:</label>
        <input type="file" id="uploadFile" name="uploadFile" required>
        <?php
        if(in_array('uploadFile', $error_list)) {
            echo '<p>Error in the provided email address</p>';
        }
        ?>
        <input type="submit" value="Submit" />
    </fieldset>
</form>

<?php
    if(isset($successful_creation) and $successful_creation === true and isset($passcode))
    {
        echo "<p>Give this code to the reciever to access their file: Password: <strong>$passcode</strong> and  Unique Code: <strong>$passphrase</strong></p>";
    }
?>

<h1>Retrieve</h1>

<form action="http://localhost/encryption/decryption.php" method="post">
    <?php ValidationRules::generate_csrf_form_token(); ?>
    <fieldset>
        <legend>Emails</legend>
        <label for="yourEmail">Your Email</label>
        <input type="email" name="yourEmail" id="yourEmail" required/>
    </fieldset>
    <fieldset>
        <legend>Passcodes</legend>
        <label for="retrievePassword">Enter the pass phrase</label>
        <input type="password" id="retrievePassword" name="password" required/>
        <label for="passphrase">Unique Code:</label>
        <input type="password" id="passphrase" name="passphrase" />
        <input type="submit" value="Retrieve" />
    </fieldset>
</form>

<?php
require_once('template/footer.php');




function validate_file_upload_input(array &$error_array, string &$sender_email, string &$receiver_email) : bool
{
    $is_valid = true;
    $s_email = ValidationRules::validate_email($_POST['ownerEmail']);
    $r_email = ValidationRules::validate_email($_POST['receiverEmail']);

    if(empty($s_email)) {
        $error_array[] = 'ownerEmail';
        $is_valid = false;
    }
    $sender_email = $s_email;

    if(!$r_email) {
        $error_array[] = 'receiverEmail';
        $is_valid = false;
    }
    $receiver_email = $r_email;

    if(empty($_FILES['uploadFile']))
    {
        $error_array[] = 'uploadFile';
        $is_valid = false;
    }

    if(empty($_POST['password']))
    {
        $error_array[] = 'password';
        $is_valid = false;
    }

    return $is_valid;
}