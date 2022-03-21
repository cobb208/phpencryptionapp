<?php
namespace Encryption\Decryption;

require_once('connection.php');
require_once('validation.php');

use DateTime;
use DateTimeZone;
use Encryption\Validation\ValidationRules;
use Exception;

if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    session_start();
    ValidationRules::validate_csrf_token();

    if(!isset($mysqli)) {
        http_response_code(500);
        exit();
    }

    $receiver_email = ValidationRules::validate_email($_POST['yourEmail']);
    if(!$receiver_email) {
        http_response_code(500);
        exit();
    }

    $password = $_POST['password'];
    $passphrase = $_POST['passphrase'];

    $stmt = $mysqli->prepare('SELECT iv, filepath, tag FROM files WHERE passphrase = ? AND receiver_email = ?  LIMIT  1');
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

    while($row = $result->fetch_assoc())
    {
        $result_filepath = $row['filepath'];
        $result_iv = $row['iv'];
        $result_tag = $row['tag'];
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
    $tmp_file = fopen('/tmp/' . $file_prefix[0] . $date_stamp . '.txt', 'w');
    $true_file_name = $file_prefix[0] . $date_stamp . '.txt';

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