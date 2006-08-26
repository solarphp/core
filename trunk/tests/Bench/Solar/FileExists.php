<?php
Solar::loadClass('Solar_Test_Bench');

class Bench_Solar_FileExists extends Solar_Test_Bench {
    
    public $file = 'Solar/View/Helper/Form.php';
    
    public function benchFopen()
    {
        $fp = @fopen($this->file, 'r', true);
        $ok = ($fp) ? true : false;
        @fclose($fp);
        return $ok;
    }
    
    public function benchExplodeInclPath()
    {
        clearstatcache();
        $path = explode(PATH_SEPARATOR, ini_get('include_path'));
        foreach ($path as $dir) {
        
            // no file requested?
            $this->file = trim($this->file);
            if (! $this->file) {
                return false;
            }

            // using an absolute path for the file?
            if ($this->file[0] == DIRECTORY_SEPARATOR) {
                return file_exists($this->file);
            }

            // using a relative path on the file
            $dir = rtrim($dir, DIRECTORY_SEPARATOR);
            if (file_exists($dir . DIRECTORY_SEPARATOR . $this->file)) {
                return true;
            }
        }
    }
    
    public function benchNoExplodeInclPath()
    {
        clearstatcache();
        
        $shortpath = str_replace('.' . PATH_SEPARATOR, '', ini_get('include_path'));
    
        if (strpos($shortpath, PATH_SEPARATOR) === false) {
        
            $dir = $shortpath;
            $this->file = trim($this->file);
        
            // no file requested?
            $this->file = trim($this->file);
            if (! $this->file) {
                return false;
            }

            // using an absolute path for the file?
            if ($this->file[0] == DIRECTORY_SEPARATOR) {
                return file_exists($this->file); // return file_exists($this->file);
            }

            // using a relative path on the file
            $dir = rtrim($dir, DIRECTORY_SEPARATOR);
            if (file_exists($dir . DIRECTORY_SEPARATOR . $this->file)) {
                return true;
            }
    
        } else {
    
            $path = explode(PATH_SEPARATOR, ini_get('include_path'));
            foreach ($path as $dir) {
                $this->file = trim($this->file);

                // no file requested?
                $this->file = trim($this->file);
                if (! $this->file) {
                    return false;
                }

                // using an absolute path for the file?
                if ($this->file[0] == DIRECTORY_SEPARATOR) {
                    return file_exists($this->file);
                }

                // using a relative path on the file
                $dir = rtrim($dir, DIRECTORY_SEPARATOR);
                if (file_exists($dir . DIRECTORY_SEPARATOR . $this->file)) {
                    return true;
                }
            }
        }
    }
}
?>