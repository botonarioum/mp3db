<?php

namespace Pipes;

use Exception;
use getID3;
use getid3_lib;
use getid3_writetags;
use Task\Task;

class ChangeId3TagsPipe extends AbstractPipe
{
    const INTRODUCE_MESSAGE = 'Change ID3 tags';

    const TAGS = ['album' => ['botonarioum.com'], 'comment' => ['Botonarioum - the largest catalog of bots']];

    const DEFAULT_TITLE = 'unknown track';

    const DEFAULT_ARTIST = 'unknown artist';

    public function __invoke(Task $task): Task
    {
        $task = parent::__invoke($task);

        $this->process($task);

        return $task;
    }

    public function process(Task $task)
    {
        $filePath = $task->getFilePath();

        $this->changeId3($filePath, self::TAGS);

        // TODO: Implement process() method.
    }

    private function changeId3(string $filePath, array $tags)
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
                $tagwriter->tagformats = $this->selectID3TagVersion($format);

                $tagwriter->overwrite_tags = true;
                $tagwriter->remove_other_tags = true;
                $tagwriter->tag_encoding = $thisFileInfo[$format]['encoding'];

                $titles = isset($thisFileInfo[$format]['comments']['title']) ? $thisFileInfo[$format]['comments']['title'] : [];
                $title = reset($titles);

                if ($title) {
                    $title = strtolower($title);
                    $title = str_replace('zaycev.net', 'botonarioum.com', $title);
                } else {
                    $title = self::DEFAULT_TITLE;
                }

                $artists = isset($thisFileInfo[$format]['comments']['artist']) ? $thisFileInfo[$format]['comments']['artist'] : [];
                $artist = reset($artists);

                if ($artist) {
                    $artist = strtolower($artist);
                    $artist = str_replace('zaycev.net', 'botonarioum.com', $artist);
                } else {
                    $artist = self::DEFAULT_ARTIST;
                }

                $tagData = $tags;
                $tagData['title'] = [$title];
                $tagData['artist'] = [$artist];

                $tagwriter->tag_data = $tagData;

                $tagwriter->WriteTags();
            }
        } catch (Exception $exception) {
            var_dump('Can not update ID3Tags');
        }
    }

    private function selectID3TagVersion($tagFormat)
    {
        if ($tagFormat === 'id3v2') {
            return array($tagFormat . ".4");
        } else {
            return array($tagFormat);
        }
    }
}
