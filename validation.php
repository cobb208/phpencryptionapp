<?php

namespace Encryption\Validation;

class ValidationRules {


    /**
     * @param string $email
     * @return string|bool
     */
    public static function validate_email(string $email) : string | bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * @return void
     * Method that validates the CSRF token sent with a request.
     */
    public static function validate_csrf_token() : void {
        if($_SERVER['REQUEST_METHOD'] === 'POST' or $_SERVER['REQUEST_METHOD'] === 'post')
        {
            $token = $_POST['csrf_token'];

            if(!$token or $token !== $_SESSION['csrf_token'])
            {
                http_response_code(403);
                exit();
            }
        }
        $_SESSION['csrf_token'] = md5(uniqid(mt_rand(), true));
    }

    public static function generate_csrf_form_token() : void {
        $csrf_token = $_SESSION['csrf_token'];
        echo "<input type='hidden' value='$csrf_token' name='csrf_token'/>";
    }
}