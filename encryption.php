<?php
/**
 * @package File Encryption
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html
 * @author Cory <cobb208@gmail.com>
 */

require_once('connection.php');
require_once('validation.php');
require_once('globals.php');

use Encryption\Validation\ValidationRules;
use Encryption\Connection\DatabaseConnection;
use Encryption\Globals\GlobalVars;


$site_title = 'Encrypt Your File';
$active_page = 'encryption';
$base_url = GlobalVars::$base_url;
$additional_header_tags = array(
    "<link rel='stylesheet' href='{$base_url}static/css/forms.css' >"
);
require_once('template/header.php');

$error_list = array();

if($_SERVER['REQUEST_METHOD'] === 'POST') {

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

        $db = new DatabaseConnection();
        $mysqli = $db->mysqli;

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
<article>
    <h1>Encrypt Your File:</h1>
    <?php
    if(isset($successful_creation) and $successful_creation === true and isset($passcode) and isset($passphrase))
    {

        $statement = <<<DOC
            <section>
                <p>Give this code to the receiver to access their file: Password: <strong>$passcode</strong> and  Unique Code: <strong>$passphrase</strong></p>
            </section>
        DOC;
        echo $statement;
    }
    ?>
    <section id="sectionForm" class="center-it">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
            <?php ValidationRules::generate_csrf_form_token(); ?>
            <fieldset>
                <legend>Emails</legend>
                <div class="form-input-block">
                    <label for="ownerEmail">Your Email:</label>
                    <input type="email" id="ownerEmail" name="ownerEmail" required placeholder="your_email@mail.com"/>
                    <?php
                    if(in_array('ownerEmail', $error_list)) {
                        echo '<p>Error in the provided email address</p>';
                    }
                    ?>
                </div>
                <div class="form-input-block">
                    <label for="receiverEmail">Their Email:</label>
                    <input type="email" id="receiverEmail" name="receiverEmail" required placeholder="their_email@mail.com"/>
                    <?php
                    if(in_array('receiverEmail', $error_list)) {
                        echo '<p>Error in the provided email address</p>';
                    }
                    ?>
                </div>

            </fieldset>
            <fieldset>
                <legend>Password</legend>
                <div class="form-input-block">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required />
                    <?php
                    if(in_array('password', $error_list)) {
                        echo '<p>Error in the provided password</p>';
                    }
                    ?>
                </div>
            </fieldset>
            <fieldset>
                <legend>File</legend>
                <div class="form-input-block">
                    <label for="uploadFile">File:</label>
                    <input type="file" id="uploadFile" name="uploadFile" required>
                    <?php
                    if(in_array('uploadFile', $error_list)) {
                        echo '<p>Error in the provided email address</p>';
                    }
                    ?>
                </div>
                <div class="form-input-block">
                    <input type="submit" value="Submit" />
                </div>
            </fieldset>
        </form>
    </section>
</article>
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