<?php
/**
 * Created by PhpStorm.
 * User: lov3catch
 * Date: 25.05.18
 * Time: 23:16
 */

class ChangeId3TagsPipe
{

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
