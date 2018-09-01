<?php declare(strict_types=1);

namespace Task;

class Task
{
    /** @var string */
    private $downloadUrl;

    /** @var string */
    private $filePath;

    /** @var  array */
    private $storages;

    /** @var string */
    private $botToken;

    /** @var int */
    private $downloadRowId;

    /** @var array */
    private $uploadResult;

    /** @var int */
    private $downloadResultRowId;

    /**
     * Task constructor.
     * @param string $downloadUrl
     * @param string $filePath
     * @param array $storages
     * @param string $botToken
     */
    public function __construct(string $downloadUrl, string $filePath, array $storages, string $botToken)
    {
        $this->downloadUrl = $downloadUrl;
        $this->filePath = $filePath;
        $this->storages = $storages;
        $this->botToken = $botToken;
    }

    /**
     * @return string
     */
    public function getDownloadUrl(): string
    {
        return $this->downloadUrl;
    }

    /**
     * @return string
     */
    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * @return array
     */
    public function getStorages(): array
    {
        return $this->storages;
    }

    /**
     * @return string
     */
    public function getBotToken(): string
    {
        return $this->botToken;
    }

    /**
     * @return int
     */
    public function getDownloadRowId(): int
    {
        return $this->downloadRowId;
    }

    /**
     * @param int $downloadRowId
     */
    public function setDownloadRowId(int $downloadRowId): void
    {
        $this->downloadRowId = $downloadRowId;
    }

    /**
     * @return array
     */
    public function getUploadResult(): array
    {
        return $this->uploadResult;
    }

    /**
     * @param array $uploadResult
     */
    public function setUploadResult(array $uploadResult): void
    {
        $this->uploadResult = $uploadResult;
    }

    /**
     * @return int
     */
    public function getDownloadResultRowId(): int
    {
        return $this->downloadResultRowId;
    }

    /**
     * @param int $downloadResultRowId
     */
    public function setDownloadResultRowId(int $downloadResultRowId): void
    {
        $this->downloadResultRowId = $downloadResultRowId;
    }
}