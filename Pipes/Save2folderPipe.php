<?php
/**
 * Created by PhpStorm.
 * User: lov3catch
 * Date: 25.05.18
 * Time: 23:15
 */

class Save2folderPipe
{

}

/**
 * @param string $filePath
 * @param string $downloadUrl
 * @return bool
 */
function save2folder(string $filePath, string $downloadUrl)
{
    $fileData = file_get_contents($downloadUrl);

    $handle = fopen($filePath, 'w');

    fwrite($handle, $fileData);
    fclose($handle);

    unset($fileData);

    return true;
}
