<?php

class KapLogging
{
    private $_logDir = 'tmp/';
    private $_key = 'testkey';
    private $_logfile;
    private $_handle;
    private $_delimeter = '<-->';
    private $_crypt = true;

    public function __construct($dir = false, $key = false)
    {
        if($dir)
            $this->_logDir = $dir;

        $this->_logfile = $dir . 'app.bin';

        if($key)
            $this->_key = $key;
        else
            $this->_crypt = false;

        $this->_handle = fopen($this->_logfile, 'a');
    }

    public function __destruct()
    {
        fclose($this->_handle);
    }

    public function clear()
    {
        fclose($this->_handle);
        $this->_handle = fopen($this->_logfile, 'w');

        return true;
    }

    public function write($user, $action, $data = false)
    {
        $msg = '['. date('Y-m-d H:i:s') .'] - ' . $user . ' - ' . $action;

        if($data)
            $msg .= ' - '.json_encode($data);

        if($this->_crypt)
            $msg = @mcrypt_ecb(MCRYPT_DES, $this->_key, $msg, MCRYPT_ENCRYPT);

        fwrite($this->_handle, $msg . $this->_delimeter);
    }

    public function readAll()
    {
        $log = file_get_contents($this->_logfile);

        $result = array();
        foreach(explode($this->_delimeter, $log) as $msg)
        {
            if($msg)
            {
                if($this->_crypt)
                    $msg = @mcrypt_ecb(MCRYPT_DES, $this->_key, $msg, MCRYPT_DECRYPT);

                $result[] = explode(' - ', $msg);
            }
        }

        return $result;
    }
}

