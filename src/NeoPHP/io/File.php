<?php

namespace NeoPHP\io;

use Exception;
use NeoPHP\core\Object;

final class File extends Object
{
    private $filename;
    
    public function __construct($filename)
    {
        $this->filename = $filename;
    }
    
    public function getFileName ()
    {
        return $this->filename;
    }
    
    public function getName ()
    {
        return basename($this->filename);
    }
    
    public function getParent ()
    {
        return dirname($this->filename);
    }
    
    public function getParentFile ()
    {
        return new File ($this->getParent());
    }
    
    public function getCanonicalPath ()
    {
        return realpath($this->filename);
    }
    
    public function getCanonicalFile ()
    {
        return new File ($this->getCanonicalPath());
    }
    
    public function delete ()
    {
        return unlink($this->filename);
    }
    
    public function exists ()
    {
        return file_exists($this->filename);
    }
    
    public function canRead ()
    {
        return is_readable($this->filename);
    }
    
    public function canWrite ()
    {
        return is_writable($this->filename);
    }
    
    public function canExecute ()
    {
        return is_executable($this->filename);
    }
    
    public function isDirectory ()
    {
        return is_dir($this->filename);
    }
    
    public function isFile ()
    {
        return is_file($this->filename);
    }
    
    public function renameTo (File $destinationFile)
    {
        return rename($this->filename, $destinationFile->getFileName());
    }
    
    public function length ()
    {
        return filesize($this->filename);
    }
    
    public function lastModified ()
    {
        return filemtime($this->filename);
    }
    
    public function setLastModified ($time=0)
    {
        return touch($this->filename, $time>0? $time : time());
    }
    
    public function getPermissions ()
    {
        return fileperms($this->filename);
    }
    
    public function setPermissions ($permissions)
    {
        return chmod($this->filename, $permissions);
    }
    
    public function mkdir ()
    {
        $created = false;
        try
        {
            if (!file_exists($this->filename))
            {
                $created = mkdir($this->filename);
            }
        }
        catch (Exception $ex)
        {
            throw new IOException ("Error creating directory \"" . $this->filename . "\"");
        }
        return $created;
    }
    
    public function mkdirs ()
    {
        $created = false;
        try
        {
            if (!file_exists($this->filename))
            {
                $created = mkdir($this->filename, 0777, true);
            }
        }
        catch (Exception $ex)
        {
            throw new IOException ("Error creating directory \"" . $this->filename . "\"");
        }
        return $created;
    }
    
    public function setContent ($content, $append=false)
    {
        try
        {
            return @file_put_contents($this->filename, $content, $append? FILE_APPEND : 0);
        }
        catch (Exception $ex)
        {
            throw new IOException("Error writing file \"" . $this->filename . "\"");
        }
    }
    
    public function getContent ()
    {
        try
        {
            return @file_get_contents($this->filename);
        }
        catch (Exception $ex)
        {
            throw new IOException("Error reading file \"" . $this->filename . "\"");
        }
    }
    
    public function appendContent ($content)
    {
        return $this->setContent($content, true);
    } 
    
    public function listDir ($recursive=false, $filter=null)
    {
        $files = [];
        $this->_list($this->filename, $recursive, $filter, function ($filename) use (&$files)
        {
            $files[] = $filename;
        });
        return $files;
    }
    
    public function listFiles ($recursive=false, $filter=null)
    {
        $files = [];
        $this->_list($this->filename, $recursive, $filter, function ($filename) use (&$files)
        {
            $files[] = new File($filename);
        });
        return $files;
    }
    
    public function iterateFiles (callable $callable, $recursive=false, $filter=null)
    {
        $this->_list($this->filename, $recursive, $filter, $callable);
    }
    
    public function hashCode()
    {
        $hashCode = null;
        if (!$this->isDirectory())
        {
            $hashCode = md5_file($this->filename);
        }
        else
        {
            $files = [];
            $this->_list($this->filename, true, null, function ($filename) use (&$files) { $files[] = md5_file($filename); });
            $hashCode = md5(implode('', $files));
        }
        return $hashCode;
    }
    
    private static function _list ($filename, $recursive=false, $filter=null, callable $callable = null)
    {
        $files = scandir($filename);
        foreach ($files as $file)
        {
            if ($file == "." || $file == "..")
                continue;
            
            $fullFilename = $filename . DIRECTORY_SEPARATOR . $file;
            if (is_dir($fullFilename))
            {
                if ($recursive)
                {
                    self::_list($fullFilename, $recursive, $filter, $callable);
                }
            }
            if ($filter != null && !preg_match ($filter, $fullFilename))
                continue;
            call_user_func($callable, $fullFilename);
        }
    }
}

?>