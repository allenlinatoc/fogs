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
    const PAGE_SELF = '1';
    
    
    
    
    
    /**
     * Converts all URL 
     * @param type $is_refresh
     */
    public static function AcceptURLQueries($is_refresh=true, $a_allowednames=array(), $a_blocknames=array())
    {
        $had_something = false;
        $_GETDATA = DATA::__ALL_GET();
        foreach($_GETDATA as $key => $value)
        {
            // if defined, then only allowed names will be accepted
            if ( count($a_allowednames)>0 && !ARRAYS::__HasValue($key, $a_allowednames) )
            {
                continue;
            }
            // if defined, then only disallowed names will not be accepted
            if ( count($a_blocknames)>0 && ARRAYS::__HasValue($key, $a_blocknames) )
            {
                continue;
            }
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
    
    /**
     * Delete parameter by page
     * @param string $paramName
     * @param const $page [PAGE_SELF]
     */
    public static function DeleteParameterByPage($paramName, $page=self::PAGE_SELF)
    {
        if ( self::Exists($page) )
        {
            $paramKey = self::__GetQualifiedKey($page);
            for ( $x=0,reset($_SESSION[$paramKey]); $x<count($_SESSION[$paramKey]); $x++,next($_SESSION[$paramKey]) )
            {
                $data = current($_SESSION[$paramKey]);
                $key = key($_SESSION[$paramKey]);
                if ( $data['name']==$paramName )
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
     * Delete multiple parameters in certain page
     * @param Array $a_parameters Array of parameter names
     * @param const $page [PAGE_SELF]
     */
    public static function DeleteParametersByPage($a_parameters, $page=self::PAGE_SELF)
    {
        foreach ( $a_parameters as $parameter )
        {
            self::DeleteParameterByPage($parameter, $page);
        }
    }
    
    /**
     * Destroy all parameters internationally, lols
     */
    public static function DestroyEverything()
    {
        foreach ( $_SESSION as $key=>$value )
        {
            while ( isset($_SESSION[$key]) && strpos($key, self::PARAM_KEY)!==FALSE ) {
                echo 'Deleting '.$key.'<br>';
                unset($_SESSION[$key]);
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
    
    public static function DropParametersFrom($page)
    {
        $paramKey = self::__GetQualifiedKey($page);
        if ( isset($_SESSION[$paramKey]) )
        {
            unset($_SESSION[$paramKey]);
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
        return isset($_SESSION[$paramKey]);
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
    
    /**
     * Get the value of certain parameter
     * @param string $paramName Name of the parameter
     * @param string $page [PAGE_GLOBAL]
     * @return mixed
     */
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
    
    public static function __GetQualifiedKey($page=self::PAGE_GLOBAL)
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
    
    /**
     * Check if this page has parameters
     * @param string $page [PAGE_GLOBAL]
     * @param Array $a_parameternames [array()]
     * @return boolean
     */
    public static function __HasParameters($page=self::PAGE_GLOBAL, $a_parameternames=array())
    {
        if (self::Exists($page))
        {
            $paramKey = self::__GetQualifiedKey($page);
            if ( array_key_exists($paramKey, $_SESSION) )
            {
                if ( is_array($_SESSION[$paramKey]) )
                {
                    if ( count($a_parameternames)==0 )
                    {
                        return count($_SESSION[$paramKey]) > 0;
                    }
                    else {
                        $paramNames = array();
                        // build the existing parameter names first
                        foreach ( $_SESSION[$paramKey] as $name=>$value )
                        {
                            array_push($paramNames, $value['name']);
                        }
                        // then, search for existence of each supplied paramenter names
                        foreach ( $a_parameternames as $name )
                        {
                            if ( array_search($name, $paramNames)===FALSE )
                            {
                                return false;
                            }
                        }
                        return true;
                    }
                }
            }
            else {
                return false;
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
        $paramKey = self::__GetQualifiedKey(Index::__GetPage());
        if ($is_includepageparams && isset($_SESSION[$paramKey]))
        {
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