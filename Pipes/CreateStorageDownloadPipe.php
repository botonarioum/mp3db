<?php

namespace Pipes;

use stdClass;
use Illuminate\Database\Capsule\Manager as Capsule;
use Task\Task;

class CreateStorageDownloadPipe implements PipeInterface
{
    private $tableName = 'storage_download';

    public function __invoke(Task $task): Task
    {
        $this->process($task);

        return $task;
    }

    public function process(Task $task)
    {
        $storage = $task->getStorage();
        $downloadUrl = $task->getDownloadUrl();

        $storageDownloadId = $this->run($storage, $downloadUrl);

        $task->setDownloadRowId($storageDownloadId);
    }

    /**
     * @param stdClass $storage
     * @param string $downloadUrl
     * @return int
     */
    private function run(stdClass $storage, string $downloadUrl)
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
        return Capsule::table($this->tableName)->insertGetId($data);
    }
}
