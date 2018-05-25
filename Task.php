<?php
/**
 * Created by PhpStorm.
 * User: lov3catch
 * Date: 25.05.18
 * Time: 23:34
 */

class Task
{

    public $downloadUrl;

    /**
     * Task constructor.
     * @param $downloadUrl
     */
    public function __construct($downloadUrl)
    {
        $this->downloadUrl = $downloadUrl;
    }
}