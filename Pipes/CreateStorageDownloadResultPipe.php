<?php

namespace Pipes;

use stdClass;
use Illuminate\Database\Capsule\Manager as Capsule;
use Task\Task;

class CreateStorageDownloadResultPipe extends AbstractPipe
{
    const INTRODUCE_MESSAGE = 'Create new download result row';

    public function __invoke(Task $task): Task
    {
        $task = parent::__invoke($task);

        $this->process($task);

        return $task;
    }

    public function process(Task $task)
    {
        $storage = $task->getStorage();
        $uploadResult = $task->getUploadResult();

        $downloadResultRowId = $this->run($storage, $uploadResult);

        $task->setDownloadResultRowId($downloadResultRowId);
    }

    /**
     * @param stdClass $storage
     * @param array $uploadResult
     * @return int
     */
    private function run(stdClass $storage, array $uploadResult)
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
}
