<?php

namespace Pipes;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp;
use stdClass;
use Task\Task;

class UploadPipe implements PipeInterface
{
    public function __invoke(Task $task): Task
    {
        $this->process($task);

        return $task;
    }

    public function process(Task $task)
    {
        $filePath = $task->getFilePath();
        $storage = $task->getStorage();
        $botToken = $task->getBotToken();

        $uploadResult = $this->run($filePath, $storage, $botToken);

        $task->setUploadResult($uploadResult);
    }

    /**
     * @param string $filePath
     * @param stdClass $storage
     * @param string $botToken
     * @return mixed
     * @throws GuzzleException
     */
    private function run(string $filePath, stdClass $storage, string $botToken)
    {
        $options = ['multipart' => [
            ['name' => 'chat_id', 'contents' => $storage->name],
            ['name' => 'audio', 'contents' => fopen($filePath, 'r')],
            ['name' => 'disable_notification', 'contents' => true]
        ]
        ];

        $uploadUrl = sprintf('https://api.telegram.org/bot%s/sendAudio', $botToken);
        $client = new GuzzleHttp\Client();
        $uploading = $client->request('POST', $uploadUrl, $options);
        $uploadResult = json_decode($uploading->getBody()->getContents(), true);

        return $uploadResult;
    }
}

