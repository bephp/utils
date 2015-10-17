<?php 
/**
 * Sets or gets an entry from the loaded config.ini file. If the $key passed
 * is 'source', it expects $value to be a path to an ini file to load. Calls
 * to config('source', 'inifile.ini') will aggregate the contents of the ini
 * file into config().
 *
 * @param string $key setting to set or get. passing null resets the config
 * @param string $value optional, If present, sets $key to this $value.
 * @param string $default optional, If present, and can not get $value return this $default.
 *
 * @return mixed|null value
 */
function config($key = null, $value = null, $default=null) {
  static $_config = array();
  // forced reset call
  if ($key === null) $_config = array();
  // if key is source, load ini file and return
  if ($key === 'source' && file_exists($value)) return $_config = array_merge($_config, parse_ini_file($value, true));
  // for all other string keys, set or get
  if (is_string($key)) {
    if ($value === null)
      return (isset($_config[$key]) ? $_config[$key] : $default);
    return ($_config[$key] = $value);
  }
  // setting multiple settings
  if (is_array($key) && array_diff_key($key, array_keys(array_keys($key))))
    $_config = array_merge($_config, $key);
}
/**
 * Returns the string contained by 'path.url' in config.ini.
 * This includes the hostname and path. If called with $path_only set to
 * true, it will return only the path section of the URL.
 *
 * @param boolean $path_only defaults to false, true returns only the path
 * @return string value pointed to by 'dispatch.url' in config.ini.
 */
function site($path_only = false) {
    if (!($url = config('site.url'))) return null;
    if ($path_only) return rtrim(parse_url($url,  PHP_URL_PATH), '/');
    return rtrim($url, '/').'/';
}
/**
 * helper function to get the pathinfo.
 */
function path() { 
    static $path;
    if (!$path) {
        // normalize routing base, if site is in sub-dir
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        // strip base from path
        if (($base = site(true)) !== null)
            $path = preg_replace('@^'.preg_quote($base).'@', '', $path);
        // if we have a routing file (no mod_rewrite), strip it from the URI
        if ($root = config('site.router'))
            $path = preg_replace('@^/?'.preg_quote(trim($root, '/')).'@i', '', $path);
    }
    return $path;
}
/**
 * Sets or gets an entry from the loaded cache file
 * is 'source', it expects $value to be a path to an ini file to load. Calls
 * to config('source', 'inifile.ini') will aggregate the contents of the ini
 * file into config().
 *
 * @param string $key setting to set or get. passing null resets the config
 * @param string $value optional, If present, sets $key to this $value.
 *
 * @return mixed|null value
 */
function cache($key, $val=null, $expire=100) {
    static $_caches = null;
    $file = config('cache.file', null, 'cache.bin');
    if (!$_caches && file_exists($file))
        $_caches = @unserialize(file_get_contents($file), true);
    if ($val && $expire){
        $_caches[$key] = array(time()+intval($expire), $val);
        file_put_contents($file, serialize($_caches));
        return $val;
    }
    return (isset($_caches[$key]) && $_caches[$key][0] > time()) ? $_caches[$key][1] : null;
}
/**
 * Wraps around $_SESSION
 *
 * @param string $name name of session variable to set
 * @param mixed $value value for the variable. Set this to null to
 *   unset the variable from the session.
 *
 * @return mixed value for the session variable
 */
function session($name, $value = null) {
    if (!isset($_SESSION)) return null;
    if (func_num_args() === 1)
      return (isset($_SESSION[$name]) ? $_SESSION[$name] : null);
    if ($value === null)
      unset($_SESSION[$name]);
    else
      $_SESSION[$name] = $value;
}
/**
 * Wraps around $_COOKIE and setcookie().
 *
 * @param string $name name of the cookie to get or set
 * @param string $value optional. value to set for the cookie
 * @param integer $expire default 1 year. expiration in seconds.
 * @param string $path default '/'. path for the cookie.
 *
 * @return string value if only the name param is passed.
 */
function cookie($name, $value = null, $expire = 31536000, $path = '/') {
  if (func_num_args() === 1)
    return (isset($_COOKIE[$name]) ? $_COOKIE[$name] : null);
  setcookie($name, $value, time() + $expire, $path);
}
/**
 * Returns the client's IP address.
 *
 * @return string client's ip address.
 */
function ip() {
    foreach(array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key)
        if (isset($_SERVER[$key])) return $_SERVER[$key];
    return '0.0.0.0';
}

