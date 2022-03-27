<?php
/**
 * @author cobb208@gmail.com
 */
namespace Encryption\Template;
require_once('validation.php');
require_once 'globals.php';
use Encryption\Validation\ValidationRules;
use Encryption\Globals\GlobalVars;

session_start();
ValidationRules::validate_csrf_token();

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="author" content="cobb208">
        <meta name="description" content="A file encryption website to share documents between others in a secure way">
        <title><?php if(isset($site_title)) echo $site_title . ' | '; ?>File transfer</title>
        <link rel="stylesheet" href="<?php echo GlobalVars::$base_url?>static/css/main.css" />
        <?php
            if(isset($additional_header_tags))
            {
                foreach ($additional_header_tags as $tag)
                {
                    echo $tag;
                }
            }
        ?>
    </head>
<body>
<main>
    <header>
        <nav id="navigationBar">
            <h1>
                File Transfer v0.2
            </h1>
            <ul>
                <li>
                    <a <?php if(isset($active_page)) GlobalVars::generate_active_nav('home', $active_page); ?> href="<?php echo GlobalVars::generate_url('home'); ?>">Home</a>
                </li>
                <li>
                    <a <?php if(isset($active_page)) GlobalVars::generate_active_nav('encryption', $active_page); ?> href="<?php echo GlobalVars::generate_url('encryption'); ?>">Encrypt</a>
                </li>
                <li>
                    <a <?php if(isset($active_page)) GlobalVars::generate_active_nav('decryption', $active_page); ?> href="<?php echo GlobalVars::generate_url('decryption'); ?>">Decrypt</a>
                </li>
            </ul>
        </nav>
    </header>