<?php

namespace App\Services;

class FolderService {

    public static function deleteFolder($folder) {
        if (!is_dir($folder)) {
            return false;
        }
    
        $files = array_diff(scandir($folder), ['.', '..']);
        foreach ($files as $file) {
            $path = "$folder/$file";
            is_dir($path) ? self::deleteFolder($path) : unlink($path);
        }
    
        return rmdir($folder);
    }

}