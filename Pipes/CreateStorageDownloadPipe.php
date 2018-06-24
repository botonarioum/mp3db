<?php

namespace Pipes;

use stdClass;
use Illuminate\Database\Capsule\Manager as Capsule;
use Task\Task;

class CreateStorageDownloadPipe extends AbstractPipe
{
    const INTRODUCE_MESSAGE = 'Create new download row';

    public function __invoke(Task $task): Task
    {
        $task = parent::__invoke($task);

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
        // Сохраняем URL загрузки (если уже есть - получаем старый)
        $downloadUrlRow = Capsule::table('storage_download_url')->where('url', $downloadUrl)->first(['id']);

        if ($downloadUrlRow) {
            $downloadUrlRowId = $downloadUrlRow->id;
        } else {
            $downloadUrlRowId = Capsule::table('storage_download_url')->insertGetId(['url' => $downloadUrl]);
        }

        // Сохраняем запись о загрузке (если уже есть - получаем старую)
        $data = [
            'storage_id' => $storage->id,
            'download_url_id' => $downloadUrlRowId
        ];

        $storageDownloadRow = Capsule::table('storage_download')
            ->where('storage_id', $data['storage_id'])
            ->where('download_url_id', $data['download_url_id'])->first(['id']);

        if ($storageDownloadRow) {
            var_dump('FOUND');
            return $storageDownloadRow->id;
        } else {
            var_dump('NOT FOUND');
            return Capsule::table('storage_download')->insertGetId($data);
        }
    }
}
