<?php



if (!@require_once (dirname(__FILE__) . DIRECTORY_SEPARATOR. 'htmlpurifier' . DIRECTORY_SEPARATOR . 'HTMLPurifier.auto.php'))
{
    die ("Unexpected error [purifier]");
}



class SwaySecurity
{
    public static $purifier_enable_id = false;
    public static $purifier_blacklist_ids = array();

    public static $purifier_allowed_elements = array();
    public static $purifier_forbidden_elements = array();

    public static $purifier_allowed_attributes = array();
    public static $purifier_forbidden_attributes = array();

    public static $purifier_forbidden_classess = array();

    /**
     * This is the default image an img tag will be pointed to if it does not have a valid src attribute
     * @var string
     */
    public static $purifier_default_invalid_image = "";

    /**
     * This is the content of the alt tag of an invalid image if the user had not previously specified an alt attribute
     * @var string
     */
    public static $purifier_default_invalid_image_alt = "";

    /**
     * If is set to true, purifier will remove span elements without attributes
     * @var type
     */
    public static $purifier_remove_span_without_attributes = false;

    public static $purifier_uri_allowed_schemes = array ('http' => true, 'https' => true);

    public static $purifier_uri_host = "";

    public static $purifier_uri_disable = false;

    public static $purifier_uri_disable_external = true;

    public static $purifier_uri_disable_external_resources = true;

    public static $purifier_uri_disable_resources = false;

    public static $purifier_uri_host_blacklist = array();

    public static $purifier_youtube = false;

    public static $purifier_color_keywords = array();

    public static $purifier_hidden_elements = array();

    private static $cacheDirectoryPath = null;

    public static function setCacheDirectory(string $cacheDirectoryPath)
    {
        if (!is_dir($cacheDirectoryPath)){
            $result = mkdir($cacheDirectoryPath);

            if (!$result){
                throw new Exception(sprintf("Cannot create directory on path: '%s'", $cacheDirectoryPath));
            }
        }

        self::$cacheDirectoryPath = $cacheDirectoryPath;
    }

    public static function CleanHTML($html, $config = null)
    {
        $cfg = HTMLPurifier_Config::createDefault();

        if ($config === null)
        {
            if (SwaySecurity::$purifier_enable_id === true)
                $cfg->set('Attr.EnableID', true);

            if (count(self::$purifier_allowed_elements) > 0)
                $cfg->set('HTML.AllowedElements', self::$purifier_allowed_elements);

            if (count(self::$purifier_forbidden_elements) > 0)
                $cfg->set('HTML.ForbiddenElements', self::$purifier_forbidden_elements);

            if (count(self::$purifier_allowed_attributes) > 0)
                $cfg->set('HTML.AllowedAttributes', self::$purifier_allowed_attributes);

            if (count(self::$purifier_forbidden_attributes) > 0)
                $cfg->set('HTML.ForbiddenAttributes', self::$purifier_forbidden_attributes);

            if (strlen(self::$purifier_default_invalid_image) > 0)
                $cfg->set('Attr.DefaultInvalidImage', self::$purifier_default_invalid_image);

            if (strlen(self::$purifier_default_invalid_image_alt) > 0)
                $cfg->set('Attr.DefaultInvalidImageAlt', self::$purifier_default_invalid_image_alt);

            if (count(self::$purifier_forbidden_classess) > 0)
                $cfg->set('Attr.ForbiddenClasses', self::$purifier_forbidden_classess);

            if (self::$purifier_remove_span_without_attributes === true)
                $cfg->set('AutoFormat.RemoveSpansWithoutAttributes', true);

            if (strlen(self::$purifier_uri_host) > 0)
                $cfg->set('URI.Host', self::$purifier_uri_host);

            if (count(self::$purifier_uri_allowed_schemes) > 0)
                $cfg->set('URI.AllowedSchemes', self::$purifier_uri_allowed_schemes);

            $cfg->set('URI.Disable', self::$purifier_uri_disable);
            $cfg->set('URI.DisableExternal', self::$purifier_uri_disable_external);
            $cfg->set('URI.DisableExternalResources', self::$purifier_uri_disable_external_resources);
            $cfg->set('URI.DisableResources', self::$purifier_uri_disable_resources);
            $cfg->set('Filter.YouTube', self::$purifier_youtube);
            $cfg->set('Cache.SerializerPath', self::$cacheDirectoryPath);

            if (count(self::$purifier_uri_host_blacklist) > 0)
                $cfg->set('URI.HostBlacklist', self::$purifier_uri_host_blacklist);

            if (count(self::$purifier_color_keywords) > 0)
                $cfg->set('Core.ColorKeywords', self::$purifier_color_keywords);

            if (count(self::$purifier_hidden_elements) > 0)
                $cfg->set('Core.HiddenElements', self::$purifier_hidden_elements);
        }

        if ($config !== null)
            $cfg = $config;



        $purifier = new HTMLPurifier($cfg);

        return $purifier->purify($html);
    }

    public static function Post($key, $config_purifier = null)
    {
        if (isset($_POST[$key]))
            return SwaySecurity::CleanHTML($_POST[$key], $config_purifier);
        else
            return false;
    }

    public static function Get($key)
    {
        if (isset($_GET[$key]))
            return SwaySecurity::CleanHTML($_GET[$key], $config_purifier);
        else
            return false;
    }

    public static function CleanPost($config_purifier = null)
    {
        foreach ($_POST as $key => $val)
            if (isset($_POST[$key]))
                $_POST[$key] = SwaySecurity::CleanHTML($val, $config_purifier);

    }

    public static function CleanGet($config_purifier = null)
    {
        foreach ($_GET as $key => $val)
            if (isset($_GET[$key]))
                $_GET[$key] = SwaySecurity::CleanHTML($val, $config_purifier);

    }


}

?>