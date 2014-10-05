<?php

final class SYS {
    
    /**
     * Associative array of viewable site pages
     * @var Array(Assoc)
     */
    static public $PAGES = array();



    
    
    /**
     * Returns an array of viewable site pages
     * @return Array(Assoc)
     */
    public static function getPages() {
        self::$PAGES = parse_ini_file(DIR::$CONFIG . 'pages.ini');
        return self::$PAGES;
    }
    
}

?>