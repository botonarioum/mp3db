<?php

namespace Pipes;

use Illuminate\Database\Capsule\Manager as Capsule;
use Task\Task;

class AttachStorageDownloadResultToStorageDownloadPipe implements PipeInterface
{
    public function __invoke(Task $task): Task
    {
        $this->process($task);

        return $task;
    }

    public function process(Task $task)
    {
        $storageDownloadId = $task->getDownloadRowId();
        $storageDownloadResultId = $task->getDownloadResultRowId();

        Capsule::table('storage_download')->where('id', $storageDownloadId)->update(['storage_result_id' => $storageDownloadResultId]);
    }
}
