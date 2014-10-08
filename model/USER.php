<?php

/**
 * Static class for User-related stuff
 *
 * @author Allen
 */
final class USER {
    
    const USER_KEY = '__usersess__';
    
    // info keys
    const USERNAME = 'username';
    const PASSWORD = 'password';
    const TIMESTAMP = 'timestamp';
    
    
    
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
        $encryptpass = self::Encryptor($password, 'ENCRYPT');
        $sql = new DB();
        $sql
                ->Select()
                ->From('users')
                ->Where('`username`="'.$username.'" '
                        . 'AND `password`="'.$encryptpass.'"');
        $result = $sql->Query();
        
        if (count($result)==0 )
        {
            return false;
        }
        else
        {
            $a_userinfos = array();
            $a_userinfos['username'] = $username;
            $a_userinfos['password'] = $encryptpass;
            $a_userinfos['timestamp'] = time();
            $_SESSION[self::USER_KEY] = $a_userinfos;
            return true;
        }
    }
    
    /**
     * Gets an information from user's login session
     * @param string $infokey [null] If null, returns all user infos in array,
     *      otherwise, returns what was specified (e.g. USER::USERNAME, etc.)
     * @return mixed
     */
    public static function __Get($infokey=null)
    {
        if ( $infokey===null )
        {
            return $_SESSION[self::USER_KEY];
        }
        return $_SESSION[self::USER_KEY][$infokey];
    }
    
    public static function __IsLoggedIn()
    {
        return array_key_exists(self::USER_KEY, $_SESSION);
    }
    
}
