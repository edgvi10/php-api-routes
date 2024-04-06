<?php

$ignore_paths = [".", "..", "autoload.php",];

function getFilesPath($path)
{
    global $ignore_paths;
    $paths = [];

    $files = scandir($path);
    $files = array_diff($files, $ignore_paths);

    foreach ($files as $file) :
        $full_path = $path . DIRECTORY_SEPARATOR . $file;

        if (is_dir($full_path)) :
            $paths = array_merge($paths, getFilesPath($full_path));
        else :
            $paths[] = $full_path;
        endif;
    endforeach;

    return $paths;
}


function autoload($path)
{
    foreach (getFilesPath($path) as $file_path) require_once($file_path);
}

autoload(__DIR__);
