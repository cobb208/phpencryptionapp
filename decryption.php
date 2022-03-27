<?php
/**
 * @package File Encryption
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html
 * @author Cory <cobb208@gmail.com>
 */

namespace Encryption\Decryption;

require_once 'connection.php';
require_once 'validation.php';
require_once 'globals.php';
use DateTime;
use DateTimeZone;
use Encryption\Globals\GlobalVars;
use Encryption\Validation\ValidationRules;
use Exception;
use Encryption\Connection\DatabaseConnection;


if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    session_start();
    ValidationRules::validate_csrf_token();
    $receiver_email = ValidationRules::validate_email($_POST['yourEmail']);
    if(!$receiver_email) {
        http_response_code(500);
        exit();
    }

    $password = $_POST['password'];
    $passphrase = $_POST['passphrase'];

    $db = new DatabaseConnection();
    $mysqli = $db->mysqli;

    $stmt = $mysqli->prepare('SELECT iv, filepath, tag, file_ext FROM files WHERE passphrase = ? AND receiver_email = ?  LIMIT  1');
    $stmt->bind_param('ss', $passphrase, $receiver_email);

    if(!$stmt->execute())
    {
        http_response_code(500);
        exit();
    }

    $result = $stmt->get_result();
    if(!$result)
    {
        http_response_code(404);
    }

    $result_filepath = '';
    $result_iv = '';
    $result_tag = '';
    $result_file_extension = '';

    while($row = $result->fetch_assoc())
    {
        $result_filepath = $row['filepath'];
        $result_iv = $row['iv'];
        $result_tag = $row['tag'];
        $result_file_extension = $row['file_ext'];
    }

    try {
        $date = new DateTime('now', new DateTimeZone('UTC'));
        $date_stamp = $date->format('is');
    } catch (Exception $e) {
        http_response_code(500);
        exit;
    }

    $cipher = 'aes-128-gcm';
    $file_contents = file_get_contents($result_filepath);
    $plain_text = openssl_decrypt($file_contents, $cipher, $password, 0, $result_iv, $result_tag);
    $file_prefix = explode("@", $receiver_email);
    $tmp_file = fopen('/tmp/' . $file_prefix[0] . $date_stamp . '.' . $result_file_extension, 'w');
    $true_file_name = $file_prefix[0] . $date_stamp . '.' . $result_file_extension;

    fwrite($tmp_file, $plain_text);

    fclose($tmp_file);

    header('Content-Type: multi-part/form-data');
    header("Content-Disposition: attachment; filename=$true_file_name");

    if(readfile('/tmp/' . $true_file_name)) {
        unlink('/tmp/' . $true_file_name);
        exit;
    }
    http_response_code(500);
    exit;
}

$site_title = 'Decrypt Your File';
$active_page = 'decryption';
$base_url = GlobalVars::$base_url;
$additional_header_tags = array(
    "<link rel='stylesheet' href='{$base_url}static/css/forms.css' >"
);
require_once 'template/header.php';


?>
<article>
    <h1>Decrypt Your File</h1>
    <section id="sectionForm" class="center-it">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <?php ValidationRules::generate_csrf_form_token(); ?>
            <fieldset>
                <legend>Emails</legend>
                <div class="form-input-block">
                    <label for="yourEmail">Your Email:</label>
                    <input type="email" name="yourEmail" id="yourEmail" required placeholder="your_email@mail.com"/>
                </div>
            </fieldset>
            <fieldset>
                <legend>Passcodes</legend>
                <div class="form-input-block">
                    <label for="retrievePassword">Enter the Pass Phrase:</label>
                    <input type="password" id="retrievePassword" name="password" required/>
                </div>
                <div class="form-input-block">
                    <label for="passphrase">Unique Code:</label>
                    <input type="password" id="passphrase" name="passphrase" />
                </div>
                <input type="submit" value="Retrieve" />
            </fieldset>
        </form>
    </section>
</article>

<?php
require_once 'template/footer.php';
