<?php

namespace NeoPHP\FileSystem;

abstract class Files {

    public static function createSymbolicLink(File $target, File $link) {
        return symlink($target->getFileName(), $link->getFileName());
    }

    public static function listDir(File $dir, $recursive = false, $filter = null) {
        return $dir->listDir($recursive, $filter);
    }

    public static function listFiles(File $dir, $recursive = false, $filter = null) {
        return $dir->listFiles($recursive, $filter);
    }

    public static function iterateFiles(File $dir, callable $callable, $recursive = false, $filter = null) {
        $dir->iterateFiles($callable, $recursive, $filter);
    }

    public static function copy(File $file, File $destinationDir) {
        $destinationFile = new File($destinationDir->getFileName() . DIRECTORY_SEPARATOR . $file->getName());
        if ($file->isDirectory()) {
            if (!$destinationFile->exists())
                $destinationFile->mkdir();
            foreach (self::listFiles($file) as $file)
                self::copy($file, $destinationFile);
        }
        else {
            copy($file->getFileName(), $destinationFile->getFileName());
        }
    }
}