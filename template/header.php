<?php
namespace Encryption\Template;
require_once('validation.php');
use Encryption\Validation\ValidationRules;

session_start();
ValidationRules::validate_csrf_token();

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>File transfer</title>
    </head>
    <body>
