<?php


/**
 * This remembers parameters for certain pages
 */
class PARAMS
{
    const PARAM_KEY = 'param_';
    
    /**
     * Global page indication
     */
    const PAGE_GLOBAL = null;
    /**
     * Self page indication
     */
    const PAGE_SELF = '^^^';
    
    
    
    
    
    /**
     * Converts all URL 
     * @param type $is_refresh
     */
    public static function AcceptURLQueries($a_allowednames=array(), $is_refresh=true)
    {
        $had_something = false;
        foreach($_GET as $key => $value)
        {
            ARRAYS::SearchSubstring($str_value, $a_haystack);
            self::Create($key, $value, self::PAGE_SELF);
            if ( !$had_something )
            {
                $had_something = true;
            }
        }
        if ( $had_something && $is_refresh )
        {
            UI::RefreshPage();
        }
    }
    
    
    /**
     * Create a parameter for this page or global
     * @param string $paramName Parameter's name
     * @param mixed $paramValue Parameter's value
     * @param string $page [PAGE_GLOBAL] If not defined, this parameter will be defined for global
     * @param bool $is_overwriteifexist [true] If existing parameters should be overwritten or not
     */
    public static function Create($paramName, $paramValue, $page=self::PAGE_GLOBAL, $is_overwriteifexist=true)
    {
        // format: 'param_PAGENAME'
        $paramKey = self::__GetQualifiedKey($page);
        if ( !isset($_SESSION[$paramKey]) )
        {
            $_SESSION[$paramKey] = array();
        }
        if ( $is_overwriteifexist )
        {
            for ( $x=0; $x<count($_SESSION[$paramKey]); $x++,next($_SESSION[$paramKey]) )
            {
                $value = current($_SESSION[$paramKey]);
                if ($value['name']===$paramName)
                {
                    unset($_SESSION[$paramKey][key($_SESSION[$paramKey])]);
                    break;
                }
            }
            if (is_array($_SESSION[$paramKey])) {
                reset($_SESSION[$paramKey]);
            }
        }
        array_push($_SESSION[$paramKey], array(
                'name' => $paramName,
                'value' => $paramValue
            )
        );
    }
    
    /**
     * Create many parameters in for a page
     * @param Array(assoc) $a_parametersData Parameter data as array of array([name]=>"",[value]=>"")
     * @param string|Array $page [PAGE_GLOBAL] What page/s will these parameters be dedicated?
     * @param bool $is_overwriteifexist [true] If overwrite anything if exists
     */
    public static function CreateMany($a_parametersData, $page=self::PAGE_GLOBAL, $is_overwriteifexist=true)
    {
        foreach($a_parametersData as $paramData)
        {
            if (is_array($page) )
            {
                foreach($page as $p)
                {
                    self::Create($paramData['name'], $paramData['value'], $p, $is_overwriteifexist);
                }
            }
            else 
            {
                self::Create($paramData['name'], $paramData['value'], $page, $is_overwriteifexist);
            }
        }
    }
    
    /**
     * Create a parameter dedicated to different pages
     * @param string $paramName Name of parameter
     * @param string $paramValue Value of parameter
     * @param Array $a_pages Array of page names
     */
    public static function CreateMultipages($paramName, $paramValue, $a_pages)
    {
        foreach ( $a_pages as $page )
        {
            self::Create($paramName, $paramValue, $page);
        }
    }
    
    /**
     * 
     * @param type $paramName
     * @param type $is_includepage
     * @return boolean
     */
    public static function DeleteParameter($paramName, $is_includepage=false)
    {
        if ( self::ExistsParam($paramName) && self::Exists() )
        {
            $paramKey = self::__GetQualifiedKey();
            for ( $x=0; $x<count($_SESSION[$paramKey]); $x++,next($_SESSION[$paramKey]) )
            {
                $data = current($_SESSION[$paramKey]);
                $key = key($_SESSION[$paramKey]);
                if ( $data['name']==$paramName )
                {
                    do {
                        unset($_SESSION[$paramKey][$key]);
                    } while ( self::ExistsParam($paramName) );
                    return true;
                }
            }
        }
        if ( $is_includepage && self::Exists(Index::__GetPage()) )
        {
            if ( self::ExistsParam($paramName, Index::__GetPage()) )
            {
                $paramKey = self::__GetQualifiedKey(Index::__GetPage());
                for ( $x=0; $x<count($_SESSION[$paramKey]); $x++,next($_SESSION[$paramKey]) )
                {
                    $data = current($_SESSION[$paramKey]);
                    $key = key($_SESSION[$paramKey]);
                    if ( $data[$paramKey]==$paramName )
                    {
                        do {
                            unset($_SESSION[$paramKey][key($_SESSION[$paramKey])]);
                        } while ( self::ExistsParam($paramName, Index::__GetPage()) );
                        return true;
                    }
                }
            }
        }
        return false;
    }
    
    public static function DeleteParameterByPage($paramName, $page=self::PAGE_GLOBAL)
    {
        if ( self::Exists($page) )
        {
            $paramKey = self::__GetQualifiedKey($page);
            for ( $x=0; $x<count($_SESSION[$paramKey]); $x++,next($_SESSION[$paramKey]) )
            {
                $data = current($_SESSION[$paramKey]);
                $key = key($_SESSION[$paramKey]);
                if ( $data['name']===$paramName )
                {
                    do {
                        unset($_SESSION[$paramKey][$key]);
                    }
                    while ( self::ExistsParam($paramName, $page) );
                }
            }
        }
    }
    
    /**
     * Destroy all parameters in this page
     */
    public static function DestroyOwn()
    {
        $paramKey = self::__GetQualifiedKey(Index::__GetPage());
        while ( isset($_SESSION[$paramKey]) )
        {
            unset($_SESSION[$paramKey]);
        }
    }
    
    /**
     * Destroy global parameters
     * @param bool $is_includepageparams [false] If page parameter should also
     *      be destroyed.
     */
    public static function DestroyGlobal($is_includepageparams=false)
    {
        $paramKey = self::__GetQualifiedKey();
        while ( isset($_SESSION[$paramKey]) )
        {
            unset($_SESSION[$paramKey]);
        }
        if ($is_includepageparams)
        {
            self::DestroyOwn();
        }
    }
    
    /**
     * If there are parameters that exists
     * @param string $page Name of the page where parameters checking will take place
     * @return bool 
     */
    public static function Exists($page=self::PAGE_GLOBAL)
    {
        $paramKey = self::__GetQualifiedKey($page);
        if ( isset($_SESSION[$paramKey]) )
        {
            return true;
        }
        return false;
    }
    
    /**
     * If a parameter specified exists for a page
     * @param string $paramName Name of parameter
     * @param string $page [PAGE_GLOBAL] Name of the page
     * @return boolean
     */
    public static function ExistsParam($paramName, $page=self::PAGE_GLOBAL)
    {
        if ( !self::Exists($page) )
        {
            return false;
        }
        
        $paramKey = self::__GetQualifiedKey($page);
        for ( $x=0; $x<count($_SESSION[$paramKey]); $x++,next($_SESSION[$paramKey]) )
        {
            $data = current($_SESSION[$paramKey]);
            if ( $data['name']==$paramName )
            {
                return true;
            }
        }
        return false;
    }
    
    public static function ExistsParamkey($paramkey)
    {
        return isset($_SESSION[$paramkey]);
    }
    
    public static function Get($paramName, $page=self::PAGE_GLOBAL)
    {
        if ( !self::Exists($page) )
        {
            return null;
        }
        
        for ( $x=0,reset($_SESSION); $x<count($_SESSION); $x++,next($_SESSION) )
        {
            $key = key($_SESSION);
            $value = current($_SESSION);
            
            // Prepare the target key
            $targetkey = self::__GetQualifiedKey($page);
            
            if ( $key === $targetkey )
            {
                $parameters = $value;
                // find the given parameter
                foreach ($parameters as $parameter)
                {
                    if ($parameter['name'] == $paramName )
                    {
                        return $parameter['value'];
                    }
                }
            }
        }
        return null;
    }
    
    protected static function __GetQualifiedKey($page=self::PAGE_GLOBAL)
    {
        if ( is_null($page) )
        {
            $page = '';
        }
        else if ( $page===self::PAGE_SELF )
        {
            $page = Index::__GetPage();
        }
        return self::PARAM_KEY . strtoupper($page);
    }
    
    public static function __HasParameters($page=self::PAGE_GLOBAL)
    {
        if (self::Exists($page))
        {
            $paramKey = self::__GetQualifiedKey($page);
            if ( is_array($_SESSION[$paramKey]) )
            {
                return count($_SESSION[$paramKey]);
            }
        }
        return false;
    }
    
    public static function __PrintParams($is_includepageparams=false)
    {
        echo '<hr>';
        $paramKey = self::__GetQualifiedKey();
        echo '<b>Global params</b><br>';
        for ( $x=0; $x<count($_SESSION[$paramKey]); $x++,next($_SESSION[$paramKey]) )
        {
            echo $x.' >> '.print_r($_SESSION[$paramKey][key($_SESSION[$paramKey])], true).'<br>';
        }
        if ($is_includepageparams)
        {
            $paramKey = self::__GetQualifiedKey(Index::__GetPage());
            echo '<b>Page ('.Index::__GetPage().') params</b><br>';
            for ( $x=0; $x<count($_SESSION[$paramKey]); $x++,next($_SESSION[$paramKey]) )
            {
                echo $x.' >> '.print_r($_SESSION[$paramKey][key($_SESSION[$paramKey])], true).'<br>';
            }
        }
        echo '<hr>';
    }
    
    public static function __PrintAllParams()
    {
        echo '<hr>';
        for ( $x=0,reset($_SESSION); $x<count($_SESSION); $x++,next($_SESSION))
        {
            $key = key($_SESSION);
            $paramKey = (strpos($key, self::PARAM_KEY)===FALSE ? null : $key);
            if ($paramKey===null)
            {
                continue;
            }
            
            $data = $_SESSION[$paramKey];
            echo '<b>'.$paramKey.'</b><br>';
            for ( $i=0; $i<count($data); $i++,next($data) )
            {
                echo $i . ' >> ' . print_r($data[key($data)],true) . '<br>';
            }
        }
        echo '<hr>';
    }
    
    
    
}


?>