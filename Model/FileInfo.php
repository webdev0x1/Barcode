<?php
namespace Akwaaba\Barcode\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Mime;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Directory\ReadInterface;

/**
 * Class FileInfo
 *
 * Provides information about requested file
 */
class FileInfo
{
    /**
     * Path in /pub/media directory
     */
    const ENTITY_MEDIA_PATH = 'barcodes';

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Mime
     */
    private $mime;

    /**
     * @var WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var ReadInterface
     */
    private $baseDirectory;


    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param Filesystem $filesystem
     * @param Mime $mime
     */
    public function __construct(
        Filesystem $filesystem,
        Mime $mime,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->filesystem = $filesystem;
        $this->mime = $mime;
        $this->_storeManager = $storeManager;
    }

    /**
     * Get WriteInterface instance
     *
     * @return WriteInterface
     */
    public function getMediaDirectory()
    {
        if ($this->mediaDirectory === null) {
            $this->mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        }
        return $this->mediaDirectory;
    }

    /**
     * Get Base Directory read instance
     *
     * @return ReadInterface
     */
    private function getBaseDirectory()
    {
        if (!isset($this->baseDirectory)) {
            $this->baseDirectory = $this->filesystem->getDirectoryRead(DirectoryList::ROOT);
        }

        return $this->baseDirectory;
    }

    /**
     * Retrieve MIME type of requested file
     *
     * @param string $fileName
     * @return string
     */
    public function getMimeType($fileName)
    {
        $filePath = $this->getFilePath($fileName);
        $absoluteFilePath = $this->getMediaDirectory()->getAbsolutePath($filePath);

        $result = $this->mime->getMimeType($absoluteFilePath);
        return $result;
    }

    /**
     * @param $fileName
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAbsolutePath($fileName)
    {

        $absoluteFilePath = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $absoluteFilePath .= $this->getFilePath($fileName);
        return $absoluteFilePath;
    }

    /**
     * Get file statistics data
     *
     * @param string $fileName
     * @return array
     */
    public function getStat($fileName)
    {
        $filePath = $this->getFilePath($fileName);

        $result = $this->getMediaDirectory()->stat($filePath);
        return $result;
    }

    /**
     * Check if the file exists
     *
     * @param string $fileName
     * @return bool
     */
    public function isExist($fileName)
    {
        $filePath = $this->getFilePath($fileName);

        $result = $this->getMediaDirectory()->isExist($filePath);
        return $result;
    }

    /**
     * Construct and return file subpath based on filename relative to media directory
     *
     * @param string $fileName
     * @return string
     */
    private function getFilePath($fileName)
    {
        $filePath = ltrim($fileName, '/');

        $mediaDirectoryRelativeSubpath = $this->getMediaDirectoryPathRelativeToBaseDirectoryPath();
        $isFileNameBeginsWithMediaDirectoryPath = $this->isBeginsWithMediaDirectoryPath($fileName);

        // if the file is not using a relative path, it resides in the paracrab/image media directory
        $fileIsInModuleMediaDir = !$isFileNameBeginsWithMediaDirectoryPath;

        if ($fileIsInModuleMediaDir) {
            $filePath = self::ENTITY_MEDIA_PATH . '/' . $filePath;
        } else {
            $filePath = substr($filePath, strlen($mediaDirectoryRelativeSubpath));
        }

        return $filePath;
    }

    /**
     * Checks for whether $fileName string begins with media directory path
     *
     * @param string $fileName
     * @return bool
     */
    public function isBeginsWithMediaDirectoryPath($fileName)
    {
        $filePath = ltrim($fileName, '/');

        $mediaDirectoryRelativeSubpath = $this->getMediaDirectoryPathRelativeToBaseDirectoryPath();
        $isFileNameBeginsWithMediaDirectoryPath = strpos($filePath, $mediaDirectoryRelativeSubpath) === 0;

        return $isFileNameBeginsWithMediaDirectoryPath;
    }

    /**
     * Get media directory subpath relative to base directory path
     *
     * @return string
     */
    private function getMediaDirectoryPathRelativeToBaseDirectoryPath()
    {
        $baseDirectoryPath = $this->getBaseDirectory()->getAbsolutePath();
        $mediaDirectoryPath = $this->getMediaDirectory()->getAbsolutePath();

        $mediaDirectoryRelativeSubpath = substr($mediaDirectoryPath, strlen($baseDirectoryPath));

        return $mediaDirectoryRelativeSubpath;
    }
}