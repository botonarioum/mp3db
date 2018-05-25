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

ini_set('memory_limit', '-1');

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

$processor = (new Processor())
    ->addPipe(new CreateStorageDownloadPipe())
    ->addPipe(new Save2folderPipe())
    ->addPipe(new ChangeId3TagsPipe())
    ->addPipe(new UploadPipe())
    ->addPipe(new CreateStorageDownloadResultPipe())
    ->addPipe(new AttachStorageDownloadResultToStorageDownloadPipe());

$processor->process(new Task('http://example.com'));

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
