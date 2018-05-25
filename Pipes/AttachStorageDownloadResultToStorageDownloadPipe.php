<?php
/**
 * Created by PhpStorm.
 * User: lov3catch
 * Date: 25.05.18
 * Time: 23:18
 */

class AttachStorageDownloadResultToStorageDownloadPipe
{

}

/**
 * @param int $storageDownloadId
 * @param int $storageDownloadResultId
 */
function attachStorageDownloadResultToStorageDownload(int $storageDownloadId, int $storageDownloadResultId)
{
    Capsule::table('storage_download')->where('id', $storageDownloadId)->update(['storage_result_id' => $storageDownloadResultId]);
}
