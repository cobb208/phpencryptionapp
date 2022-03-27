<?php
/**
 * @package File Encryption
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html
 * @author Cory <cobb208@gmail.com>
 */
namespace Encryption\Globals;

class GlobalVars
{

    /**
     * @var string Base Url of website change to what the website is
     */
    public static string $base_url = 'http://localhost/encryption/';

    /**
     * @var array|string[]
     * Add Paths to array to be used with generate_url()
     */
    public static array $url_table = array(
        'home' => 'index.php',
        'encryption' => 'encryption.php',
        'decryption' => 'decryption.php'
    );

    /**
     * @param string $path
     * The path variable you want.
     * @return string
     * If path exists will return full path as string
     */
    public static function generate_url(string $path) : string
    {
        return GlobalVars::$base_url . GlobalVars::$url_table[$path];
    }

    public static function generate_active_nav(string $path, $actual_path) : void
    {
        if($path === $actual_path)
        {
            echo "class='activeNav'";
        }
    }
}