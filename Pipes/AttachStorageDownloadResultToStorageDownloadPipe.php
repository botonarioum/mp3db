<?php

namespace Pipes;

use Illuminate\Database\Capsule\Manager as Capsule;
use Task\Task;

class AttachStorageDownloadResultToStorageDownloadPipe extends AbstractPipe
{
    const INTRODUCE_MESSAGE = 'Attach result row to download row';

    public function __invoke(Task $task): Task
    {
        $task = parent::__invoke($task);

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
