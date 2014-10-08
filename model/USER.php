<?php

/**
 * Static class for User-related stuff
 *
 * @author Allen
 */
final class USER {
    
    const USER_KEY = '__usersess__';
    
        /**
     * Encrypts or decrypts sensitive (binary) data
     * @param String $data The data to be processed
     * @param String $str_mode Choose: "ENCRYPT" or "DECRYPT"
     */
    public static function Encryptor($data, $str_mode) {
        $mode = strtoupper($str_mode);
        if ($mode == 'ENCRYPT') {
            $encrypted = base64_encode(
                    bin2hex(
                            strrev(
                                    base64_encode(
                                            bin2hex($data)))));
            $encrypted = str_replace('=', '@', $encrypted);
            return $encrypted;
        } else {
            $data = str_replace('@', '=', $data);
            $decrypted = hex2bin(base64_decode(strrev(hex2bin(base64_decode($data)))));
            return $decrypted;
        }
    }
    
    public static function Logout()
    {
        if ( self::__IsLoggedIn() )
        {
            unset($_SESSION[self::USER_KEY]);
        }
    }
    
    public static function Login($username, $password)
    {
        $a_userinfos = array();
        $a_userinfos['username'] = $username;
        $a_userinfos['password'] = self::Encryptor($password, 'ENCRYPT');
        $a_userinfos['timestamp'] = time();
        $_SESSION[self::USER_KEY] = $a_userinfos;
    }
    
    public static function __IsLoggedIn()
    {
        return key_exists(self::USER_KEY, $_SESSION);
    }
    
}
