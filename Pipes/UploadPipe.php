<?php

namespace Pipes;

use GuzzleHttp;
use GuzzleHttp\Exception\GuzzleException;
use stdClass;
use Task\Task;

class UploadPipe extends AbstractPipe
{
    const INTRODUCE_MESSAGE = 'Upload to telegram-storage';

    const SEND_AUDIO_URL = 'https://api.telegram.org/bot%s/sendAudio';

    /** @var GuzzleHttp\Client */
    private $httpClient;

    public function __invoke(Task $task): Task
    {
        $task = parent::__invoke($task);

        $this->process($task);

        return $task;
    }

    /**
     * @param GuzzleHttp\Client $httpClient
     * @return UploadPipe
     */
    public function setHttpClient(GuzzleHttp\Client $httpClient): UploadPipe
    {
        $this->httpClient = $httpClient;
        return $this;
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

        $uploadUrl = sprintf(self::SEND_AUDIO_URL, $botToken);
        $uploading = $this->httpClient->request('POST', $uploadUrl, $options);
        $uploadResult = json_decode($uploading->getBody()->getContents(), true);

        return $uploadResult;
    }
}

