<?php # namespace Eckinox;

// Functions
function array_overwrite_recursive($arr1, $arr2) {
    foreach ($arr2 as $key => $value) {
        if (array_key_exists($key, $arr1) && is_array($value)) {
            $arr1[$key] = array_overwrite_recursive($arr1[$key], $arr2[$key]);
        } else {
            $arr1[$key] = $value;
        }
    }

    return $arr1;
}

// Window doesn't support UTF-8 locales, we need a wrapper for strftime()
function nex_strftime($format, $timestamp = null) {
    $res = strftime($format, $timestamp);

    if (stristr(PHP_OS, 'WIN') !== false) {
        $res = utf8_encode($res);
    }

    return $res;
}


# Support for PHP-FPM
if (!function_exists('getallheaders')) {
    function getallheaders() {
        static $headers = [];
       
        if ($headers) return $headers;
        
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        
        return $headers;
    }
}
