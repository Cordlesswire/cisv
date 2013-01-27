<?php

/**
 * Simple file-based PHP cache class
 *
 * @package default
 * @author Jon Gales
 * @link http://www.jongales.com/blog/2009/02/18/simple-file-based-php-cache-class/
 */
class JG_Cache {

    function __construct($dir) {
        $this->dir = $dir;
    }
    
    /**
     * Creates the path the the cache file and hashes the name
     * Modified by Baylor Rae' to work with WuPhooey
     *
     * @param string $key 
     * @return void
     * @author Jon Gales
     */
    private function _name($key) {
        if( !$salt = get_option('WuPhooey-secret_key') ) {
          $time = time();
          add_option('WuPhooey-secret_key', $time);
          $salt = $time;
        }
        return sprintf("%s/%s", $this->dir, sha1($salt . $key));
    }

    public function get($key, $expiration = 3600) {

        if ( !is_dir($this->dir) OR !is_writable($this->dir)) {
            return FALSE;
        }

        $cache_path = $this->_name($key);

        if (!@file_exists($cache_path)) {
            return FALSE;
        }

        if (filemtime($cache_path) < (time() - $expiration)) {
            $this->clear($key);
            return FALSE;
        }

        if (!$fp = @fopen($cache_path, 'rb')) {
            return FALSE;
        }

        flock($fp, LOCK_SH);

        $cache = '';

        if (filesize($cache_path) > 0) {
            $cache = unserialize(fread($fp, filesize($cache_path)));
        }
        else {
            $cache = NULL;
        }

        flock($fp, LOCK_UN);
        fclose($fp);

        return $cache;
    }

    public function set($key, $data) {

        if ( !is_dir($this->dir) OR !is_writable($this->dir)) {
            return FALSE;
        }

        $cache_path = $this->_name($key);

        if ( ! $fp = fopen($cache_path, 'wb')) {
            return FALSE;
        }

        if (flock($fp, LOCK_EX)) {
            fwrite($fp, serialize($data));
            flock($fp, LOCK_UN);
        }
        else {
            return FALSE;
        }
        fclose($fp);
        @chmod($cache_path, 0777);
        return TRUE;
    }

    public function clear($key) {
        $cache_path = $this->_name($key);

        if (file_exists($cache_path)) {
            unlink($cache_path);
            return TRUE;
        }

        return FALSE;
    }
}


?>