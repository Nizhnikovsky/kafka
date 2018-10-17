<?php

namespace Woxapp\Scaffold\Utility;

class FileUtility
{
    public static function transformFiles()
    {
        if (!$_FILES) {
            return [];
        }

        $result = [];

        foreach ($_FILES as $group => $files) {
            if (!is_array($files['tmp_name'])) {
                $result[$group] = $files;
                continue;
            }

            $result[$group] = self::transform($files);
        }

        return $_FILES = $result;
    }

    private static function transform(array $files)
    {
        $result = [];

        foreach (array_keys($files['name']) as $key) {
            $result[$key] = [
                'name' => $files['name'][$key],
                'type' => $files['type'][$key],
                'tmp_name' => $files['tmp_name'][$key],
                'error' => $files['error'][$key],
                'size' => $files['size'][$key],
            ];
        }

        return $result;
    }
}
