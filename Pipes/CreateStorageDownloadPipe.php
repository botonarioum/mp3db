<?php
/**
 * Created by PhpStorm.
 * User: lov3catch
 * Date: 25.05.18
 * Time: 23:15
 */

class CreateStorageDownloadPipe
{

}
/**
 * @param stdClass $storage
 * @param string $downloadUrl
 * @return int
 */
function createStorageDownload(stdClass $storage, string $downloadUrl)
{
    $downloadUrlRow = Capsule::table('storage_download_url')->where('url', $downloadUrl)->first(['id']);

    if ($downloadUrlRow) {
        $downloadUrlRowId = $downloadUrlRow->id;
    } else {
        $downloadUrlRowId = Capsule::table('storage_download_url')->insertGetId(['url' => $downloadUrl]);
    }

    $data = [
        'storage_id' => $storage->id,
        'download_url_id' => $downloadUrlRowId
    ];
    return Capsule::table('storage_download')->insertGetId($data);
}