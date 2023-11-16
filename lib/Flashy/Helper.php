<?php

namespace Flashy;

// TODO [low] needs to switch the flashy_id to fls_id = contact_id

use Closure;
use Exception;

class Helper
{
    public static $cookie;

    /**
     * @var string
     */
    public static $path;

    /**
     * @param $name
     * @return mixed|null
     */
    public static function getCookie($name)
    {
        if( !isset($_COOKIE[$name]) || $_COOKIE[$name] == "" )
        {
            return null;
        }

        return $_COOKIE[$name];
    }

    /**
     * @return mixed|null
     */
    public static function getContactId()
    {
        return self::getCookie("flashy_id");
    }

    /**
     * Get contact attribution
     * @return string|null
     */
    public static function getAttribution()
    {
        $attribution = self::getCookie("attribution");

        if( !$attribution )
        {
            return null;
        }

        return implode(">", json_decode($attribution));
    }

    /**
     * @param $print
     */
    public static function dd($print)
    {
        var_dump($print);
        exit;
    }

    /**
     * Delete the thunder cookie
     */
    public static function clearThunderCookie()
    {
        setcookie("flashy_thunder", "", time()-3600, "/");
    }

    /**
     * @param $contact
     */
    public static function setContact($contact)
    {
        try {
            if( gettype($contact) === "array" )
            {
                if( isset($contact['email']) )
                    $contact_id = base64_encode($contact['email']);
            }
            else
            {
                $contact_id = $contact;
            }

            if( isset($contact_id) )
            {
                Helper::log("Create cookie: flashy_id : " . $contact_id);

                self::$cookie["flashy_id"] = $contact_id;

                setcookie("flashy_id", $contact_id, time() + (360 * 24 * 60 * 60), "/");
            }
        }
        catch( Exception $e )
        {
            Helper::log("Flashy was not able to create a cookie: " . $e->getMessage());
            Helper::log($e->getTraceAsString());
        }
    }

    /**
     * @param null $contact_id
     * @return mixed|null
     */
    public static function getContact($contact_id = null)
    {
        return self::getCookie("flashy_id");
    }

    /**
     * @param $message
     */
    public static function log($message)
    {
        if( gettype($message) === "array" )
        {
            $message = json_encode($message);
        }

        $message = $message . "\n";

        file_put_contents(self::getLogPath(), $message, FILE_APPEND | LOCK_EX);
    }


    /**
     * Get Log Path
     */
    public static function getLogPath()
    {
        if( !self::$path )
        {
            return __DIR__ . "/../../log/debug.log";
        }

        return self::$path;
    }

    /**
     * @param $key
     * @param array $array
     * @param null $default
     * @return mixed|null
     */
    public static function get($key, array $array, $default = null)
    {
        if( ! isset($array[$key]) )
        {
            return $default;
        }

        return $array[$key];
    }

    /**
     * @param $string
     * @return mixed
     */
    public static function getFakeCookie($string)
    {
        if( !isset(self::$cookie[$string]) )
        {
            return null;
        }

        return self::$cookie[$string];
    }

    /**
     * Forget the connected contact
     */
    public function forget()
    {
        setcookie("flashy_id", "", time()-3600, "/");
    }

    /**
     * @param Closure $func
     * @return mixed
     */
    public static function tryOrLog(Closure $func)
    {
        if( phpversion() > 7 )
        {
            try {
                return $func();
            }
            catch ( \Throwable $e )
            {
                self::log("Was not able to do something safely: {$e->getMessage()} \n " . $e->getTraceAsString());
            }
        }
        else
        {
            try {
                return $func();
            }
            catch ( Exception $e )
            {
                self::log("Was not able to do something safely: {$e->getMessage()} \n " . $e->getTraceAsString());
            }
        }

        return null;
    }

    /**
     * @param $haystack
     * @param $needle
     * @return bool
     */
    public static function contains($haystack, $needle)
    {
        if( gettype($needle) !== 'array' )
            return strpos($haystack, $needle) !== false;

        foreach( $needle as $part )
        {
            if( strpos($haystack, $part) !== false )
                return true;
        }

        return false;
    }

}
