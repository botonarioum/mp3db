<?php declare(strict_types=1);

ini_set('memory_limit', '-1');

require __DIR__ . '/vendor/autoload.php';

require __DIR__ . '/Task/Task.php';
require __DIR__ . '/Pipes/PipeInterface.php';
require __DIR__ . '/Pipes/AbstractPipe.php';
require __DIR__ . '/Pipes/ShowSeparatorPipe.php';
require __DIR__ . '/Pipes/AttachStorageDownloadResultToStorageDownloadPipe.php';
require __DIR__ . '/Pipes/CreateStorageDownloadPipe.php';
require __DIR__ . '/Pipes/CreateStorageDownloadResultPipe.php';
require __DIR__ . '/Pipes/Save2folderPipe.php';
require __DIR__ . '/Pipes/ChangeId3TagsPipe.php';
require __DIR__ . '/Pipes/UploadPipe.php';
require __DIR__ . '/Pipes/RemoveTempFilePipe.php';

use League\Pipeline\Pipeline;
use Illuminate\Database\Capsule\Manager as Capsule;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Task\Task;
use Pipes\ShowSeparatorPipe;
use Pipes\CreateStorageDownloadPipe;
use Pipes\CreateStorageDownloadResultPipe;
use Pipes\Save2folderpipe;
use Pipes\ChangeId3TagsPipe;
use Pipes\UploadPipe;
use Pipes\AttachStorageDownloadResultToStorageDownloadPipe;
use Pipes\RemoveTempFilePipe;

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

$capsule = new Capsule;
$capsule->addConnection($databaseCredentials);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$storageList = Capsule::table('storage')->get()->toArray();

$createStorageDownloadPipe = (new CreateStorageDownloadPipe);
$save2folderPipe = (new Save2folderPipe);
$changeId3TagsPipe = (new ChangeId3TagsPipe);
$uploadPipe = (new UploadPipe)->setHttpClient(new GuzzleHttp\Client());
$createStorageDownloadResultPipe = (new CreateStorageDownloadResultPipe);
$attachStorageDownloadResultToStorageDownloadPipe = (new AttachStorageDownloadResultToStorageDownloadPipe);
$temporaryFileKiller = (new RemoveTempFilePipe);
$separatorPipe = (new ShowSeparatorPipe);

$pipeline = (new Pipeline)
    ->pipe($createStorageDownloadPipe)
    ->pipe($save2folderPipe)
    ->pipe($changeId3TagsPipe)
    ->pipe($uploadPipe)
    ->pipe($createStorageDownloadResultPipe)
    ->pipe($attachStorageDownloadResultToStorageDownloadPipe)
    ->pipe($temporaryFileKiller)
    ->pipe($separatorPipe);

function handleForAll(string $filePath, string $downloadUrl, array $storageList, string $botToken, Pipeline $pipeline)
{
    foreach ($storageList as $storage) {
        $task = new Task($downloadUrl, $filePath, $storage, $botToken);
        try {
            $pipeline->process($task);
        } catch (Exception $exception) {
            // Do not show exceptions
            // var_dump($exception->getMessage());
        }
    }
}
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

$callback = function (AMQPMessage $message) use ($storageList, $botToken, $pipeline) {
    $payload = json_decode($message->getBody(), true);
    $downloadUrl = $payload['payload']['url'];

    $fileName = generateFilename();

    handleForAll($fileName, $downloadUrl, $storageList, $botToken, $pipeline);
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
