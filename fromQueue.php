<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/Entities/Storage.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

define('DEFAULT_TITLE', 'unknown track');
define('DEFAULT_ARTIST', 'unknown artist');

$dotenv = new \Symfony\Component\Dotenv\Dotenv();
$dotenv->load('.env');

$botToken = getenv('BOT_TOKEN');

$amqpConnectSettings = [
    'queue' => getenv('QUEUE_NAME')
];

$databaseCredentials = [
    'driver' => getenv('DB_DRIVER'),
    'host' => getenv('DB_HOST'),
    'database' => getenv('DB_DATABASE'),
    'username' => getenv('DB_USERNAME'),
    'password' => getenv('DB_PASSWORD'),
    'charset' => getenv('DB_CHARSET'),
    'collation' => getenv('DB_COLLATION'),
    'prefix' => '',
];

ini_set('memory_limit', '-1')

$capsule = new Capsule;

$capsule->addConnection($databaseCredentials);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$storageList = Capsule::table('storage')->where('id', '<', 100)->get()->toArray();

/**
 * @param string $basePath
 * @param string $storageDirectory
 * @return string
 */
function generateFilename(string $basePath = __DIR__, string $storageDirectory = 'downloads')
{
    $fileName = uniqid('file_');
    $fileExtension = 'mp3';

    return sprintf('%s/%s/%s.%s', $basePath, $storageDirectory, $fileName, $fileExtension);
}

/**
 * @param string $filePath
 * @param string $downloadUrl
 * @return bool
 */
function save2folder(string $filePath, string $downloadUrl)
{

//    $fileData = file_get_contents($downloadUrl);

    file_put_contents($filePath, file_get_contents($downloadUrl));

//    $handle = fopen($filePath, 'w');
//
//    fwrite($handle, $fileData);
//    fclose($handle);
//
//    unset($fileData);

    return true;
}

;


/**
 * @param stdClass $storage
 * @param string $downloadUrl
 * @return int
 */
function createStorageDownload(stdClass $storage, string $downloadUrl)
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
    return Capsule::table('storage_download')->insertGetId($data);
}

;

/**
 * @param stdClass $storage
 * @param array $uploadResult
 * @return int
 */
function createStorageDownloadResult(stdClass $storage, array $uploadResult)
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

;

/**
 * @param int $storageDownloadId
 * @param int $storageDownloadResultId
 */
function attachStorageDownloadResultToStorageDownload(int $storageDownloadId, int $storageDownloadResultId)
{
    Capsule::table('storage_download')->where('id', $storageDownloadId)->update(['storage_result_id' => $storageDownloadResultId]);
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
    $fileWithData = fopen($filePath, 'r');
    
    $options = ['multipart' => [
        ['name' => 'chat_id', 'contents' => $storage->name],
        ['name' => 'audio', 'contents' => $fileWithData],
        ['name' => 'disable_notification', 'contents' => true]
    ]
    ];

    $uploadUrl = sprintf('https://api.telegram.org/bot%s/sendAudio', $botToken);
    // $client = (new GuzzleHttp\Client());
    $uploading = (new GuzzleHttp\Client())->request('POST', $uploadUrl, $options);
    $uploadResult = json_decode($uploading->getBody()->getContents(), true);

    fclose($fileWithData);
    return $uploadResult;
}

function handle(stdClass $storage, string $filePath, string $downloadUrl, string $botToken)
{
    // Create new download row
    $downloadRowId = createStorageDownload($storage, $downloadUrl);

    // Save file to local folder
    save2folder($filePath, $downloadUrl);

    // Change Id3
    changeId3($filePath, ['album' => ['botonarioum.com'], 'comment' => ['Botonarioum - the largest catalog of bots']]);

    // Upload to storage
    $uploadResult = upload($filePath, $storage, $botToken);

    // Create new download result row
    $downloadResultRowId = createStorageDownloadResult($storage, $uploadResult);

    // Attach result row to download row
    attachStorageDownloadResultToStorageDownload($downloadRowId, $downloadResultRowId);

}

function handleForAll(string $filePath, string $downloadUrl, array $storageList, string $botToken)
{
    foreach ($storageList as $storage) {
        try {
            handle($storage, $filePath, $downloadUrl, $botToken);
        } catch (Exception $exception) {
            var_dump($exception->getMessage());
        }
    }
}

function selectID3TagVersion($tagFormat)
{
    if ($tagFormat === 'id3v2') {
        return array($tagFormat . ".4");
    } else {
        return array($tagFormat);
    }
}

function changeId3(string $filePath, array $tags)
{
    try {
        $TaggingFormat = 'UTF-8';

        // Initialize getID3 engine
        $getID3 = new getID3;
        $getID3->setOption(array('encoding' => $TaggingFormat));

        $thisFileInfo = $getID3->analyze($filePath);

        getid3_lib::IncludeDependency(GETID3_INCLUDEPATH . 'write.php', __FILE__, true);

        $existFormats = array_keys($thisFileInfo['tags']);

        foreach ($existFormats as $format) {

            $tagwriter = new getid3_writetags;

            $tagwriter->filename = $filePath;
            $tagwriter->tagformats = selectID3TagVersion($format);

            $tagwriter->overwrite_tags = true;
            $tagwriter->remove_other_tags = true;
            $tagwriter->tag_encoding = $thisFileInfo[$format]['encoding'];

            $titles= isset($thisFileInfo[$format]['comments']['title']) ? $thisFileInfo[$format]['comments']['title'] : [];
            $title = reset($titles);

            if ($title) {
                $title = strtolower($title);
                $title = str_replace('zaycev.net', 'botonarioum.com', $title);
            } else {
                $title = 'unknown track';
            }

//            unknown artist
//            unknown track

            $artists = isset($thisFileInfo[$format]['comments']['artist']) ? $thisFileInfo[$format]['comments']['artist'] : [];
            $artist = reset($artists);

            if ($artist) {
                $artist = strtolower($artist);
                $artist = str_replace('zaycev.net', 'botonarioum.com', $artist);
            } else {
                $artist = 'unknown artist';
            }

            $tagData = $tags;
            $tagData['title'] = [$title];
            $tagData['artist'] = [$artist];

            $tagwriter->tag_data = $tagData;

            $tagwriter->WriteTags();
        }
    } catch (Exception $exception) {
        var_dump($exception->getMessage());
    }
}

$callback = function (AMQPMessage $message) use ($storageList, $botToken) {
    var_dump('download');

    $payload = json_decode($message->getBody(), true);
    $downloadUrl = $payload['payload']['url'];

    $fileName = generateFilename();

    var_dump('Start handle');
    handleForAll($fileName, $downloadUrl, $storageList, $botToken);
    var_dump('Finish handle');
    unlink($fileName);
};

$url = parse_url(getenv('CLOUDAMQP_URL'));
$connection = new AMQPStreamConnection($url['host'], 5672, $url['user'], $url['pass'], substr($url['path'], 1));

$channel = $connection->channel();

$channel->basic_consume($amqpConnectSettings['queue'], '', false, true, false, false, $callback);

while (count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();
