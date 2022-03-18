<?php
require_once('connection.php');

if(isset($_POST['yourEmail']) and isset($_POST['password']))
{
    $receiver_email = filter_var($_POST['yourEmail'], FILTER_SANITIZE_EMAIL);
    $passphrase = hash('sha256', $_POST['password']);

    if(!isset($sql_hostname) or !isset($sql_username) or !isset($sql_password) or !isset($sql_database)) { die; }
    $mysqli = new mysqli($sql_hostname, $sql_username, $sql_password, $sql_database);

    $sql =
        "SELECT iv, filepath, tag FROM files WHERE passcode = '" . $passphrase . "' AND receiver_email = '" . $receiver_email . "' LIMIT 1";


    $result = $mysqli->query($sql);

    $result_filepath = '';
    $result_iv = '';
    $result_tag = '';

    if($result->num_rows > 0)
    {
        while($row = $result->fetch_assoc())
        {
            $result_filepath = $row['filepath'];
            $result_iv = $row['iv'];
            $result_tag = $row['tag'];
        }
    }

    $cipher = 'aes-128-gcm';

    $file_contents = file_get_contents($result_filepath);

    $plain_text = openssl_decrypt($file_contents, $cipher, $passphrase, $options=0, $result_iv, $result_tag);

    $file_prefix = explode("@", $receiver_email);

    $date = new DateTime('now', new DateTimeZone('UTC'));
    $date_stamp = $date->format('is');

    $tmp_file = fopen('/tmp/' . $file_prefix[0] . $date_stamp . '.txt', 'w');
    $true_file_name = $file_prefix[0] . $date_stamp . '.txt';

    fwrite($tmp_file, $plain_text);

    fclose($tmp_file);

    $file_name = basename($tmp_file['name']);

    header('Content-Type: multi-part/form-data');
    header("Content-Disposition: attachment; filename=$true_file_name");

    readfile('/tmp/' . $true_file_name);
    unlink('/tmp/' . $true_file_name);


    exit;

}