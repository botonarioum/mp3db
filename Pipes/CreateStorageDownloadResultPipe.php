<?php
/**
 * Created by PhpStorm.
 * User: lov3catch
 * Date: 25.05.18
 * Time: 23:18
 */

class CreateStorageDownloadResultPipe
{

}

/**
 * @param stdClass $storage
 * @param array $uploadResult
 * @return int
 */
function createStorageDownloadResult(stdClass $storage, array $uploadResult)
{
    $performer = $uploadResult['result']['audio']['performer'] ?? DEFAULT_ARTIST;
    $title = $uploadResult['result']['audio']['title'] ?? DEFAULT_TITLE;

    $data = [
        'storage_id' => $storage->id,
        'message_id' => $uploadResult['result']['message_id'],
        'file_id' => $uploadResult['result']['audio']['file_id'],
        'title' => mb_strtolower($title),
        'performer' => mb_strtolower($performer),
    ];
    return Capsule::table('storage_download_result')->insertGetId($data);
}
