<?php

namespace Pipes;

use Task\Task;

class Save2folderPipe implements PipeInterface
{
    public function __invoke(Task $task): Task
    {
        $this->process($task);

        return $task;
    }

    public function process(Task $task)
    {
        $filePath = $task->getFilePath();
        $downloadUrl = $task->getDownloadUrl();

        $this->run($filePath, $downloadUrl);
    }

    /**
     * @param string $filePath
     * @param string $downloadUrl
     * @return bool
     */
    private function run(string $filePath, string $downloadUrl)
    {
        $fileData = file_get_contents($downloadUrl);

        $handle = fopen($filePath, 'w');

        fwrite($handle, $fileData);
        fclose($handle);

        unset($fileData);

        return true;
    }
}
