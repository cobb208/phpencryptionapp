<?php
namespace Encryption\Globals;

if(strtoupper($_SERVER['REQUEST_METHOD']) == 'POST')
{
    exit;
}



class GlobalVars
{
    public static string $base_url = 'http://localhost/';
}