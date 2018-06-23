<?php
/**
 * Created by PhpStorm.
 * User: lov3catch
 * Date: 25.05.18
 * Time: 23:34
 */

namespace Task;

class Task
{
    private $downloadUrl;

    private $filePath;

    private $storage;

    private $botToken;

    private $downloadRowId;

    private $uploadResult;

    private $downloadResultRowId;

    /**
     * Task constructor.
     * @param $downloadUrl
     * @param $filePath
     * @param $storage
     * @param $botToken
     */
    public function __construct($downloadUrl, $filePath, $storage, $botToken)
    {
        $this->downloadUrl = $downloadUrl;
        $this->filePath = $filePath;
        $this->storage = $storage;
        $this->botToken = $botToken;
    }

    /**
     * @return mixed
     */
    public function getDownloadUrl()
    {
        return $this->downloadUrl;
    }

    /**
     * @return mixed
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * @return mixed
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @return mixed
     */
    public function getBotToken()
    {
        return $this->botToken;
    }

    /**
     * @return mixed
     */
    public function getDownloadRowId()
    {
        return $this->downloadRowId;
    }

    /**
     * @param mixed $downloadRowId
     */
    public function setDownloadRowId($downloadRowId): void
    {
        $this->downloadRowId = $downloadRowId;
    }

    /**
     * @return mixed
     */
    public function getUploadResult()
    {
        return $this->uploadResult;
    }

    /**
     * @param mixed $uploadResult
     */
    public function setUploadResult($uploadResult): void
    {
        $this->uploadResult = $uploadResult;
    }

    /**
     * @return mixed
     */
    public function getDownloadResultRowId()
    {
        return $this->downloadResultRowId;
    }

    /**
     * @param mixed $downloadResultRowId
     */
    public function setDownloadResultRowId($downloadResultRowId): void
    {
        $this->downloadResultRowId = $downloadResultRowId;
    }
}