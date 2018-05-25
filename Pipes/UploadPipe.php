<?php
/**
 * Created by PhpStorm.
 * User: lov3catch
 * Date: 25.05.18
 * Time: 23:16
 */

class UploadPipe
{

}

/**
 * @param string $filePath
 * @param stdClass $storage
 * @param string $botToken
 * @return mixed
 * @throws \GuzzleHttp\Exception\GuzzleException
 */
function upload(string $filePath, stdClass $storage, string $botToken)
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
