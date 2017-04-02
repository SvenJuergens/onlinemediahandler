<?php
namespace SvenJuergens\Onlinemediahandler\Resource\OnlineMedia\Helpers;
/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use TYPO3\CMS\Core\Resource\OnlineMedia\Helpers\AbstractOEmbedHelper;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class InstagramHelper extends AbstractOEmbedHelper {

    /**
     * @var null $oEmbedData
     */
    public $oEmbedData = null;

    /**
     * @param string $url
     * @param Folder $targetFolder
     * @return File
     */
    public function transformUrlToFile($url, Folder $targetFolder)
    {
        $imageId = null;
        if (preg_match('/instagram\.com\/p\/*([a-zA-Z0-9]+)/i', $url, $matches)) {
            $imageId = $matches[1];
        }
        if (empty($imageId)) {
            return null;
        }
        return $this->transformMediaIdToFile($imageId, $targetFolder, $this->extension);
    }
    /**
     * Transform mediaId to File
     *
     * @param string $mediaId
     * @param Folder $targetFolder
     * @param string $fileExtension
     * @return File
     */
    protected function transformMediaIdToFile($mediaId, Folder $targetFolder, $fileExtension)
    {
        $file = $this->findExistingFileByOnlineMediaId($mediaId, $targetFolder, $fileExtension);
        // no existing file create new
        if ($file === null) {
            $oEmbed = $this->getOEmbedData($mediaId);
            if (!empty($oEmbed) && isset($oEmbed['title'])) {
                $fileName = $oEmbed['author_name'] . '-' . $oEmbed['title'] . '.' . $fileExtension;
            } else {
                $fileName = $mediaId . '.' . $fileExtension;
            }
            $file = $this->createNewFile($targetFolder, $fileName, $mediaId);
        }
        return $file;
    }
    /**
     * Get meta data for OnlineMedia item
     * Using the meta data from oEmbed
     *
     * @param File $file
     * @return array with metadata
     */
    public function getMetaData(File $file)
    {
        $metadata = [];
        $oEmbed = $this->getOEmbedData($this->getOnlineMediaId($file));
        if ($oEmbed) {
            $metadata['width'] = (int)$oEmbed['width'];
            $metadata['height'] = (int)$oEmbed['height'];
            if (empty($file->getProperty('title')) && isset($oEmbed['title'])) {
                $metadata['title'] = strip_tags($oEmbed['title']);
            }
            $metadata['author'] = $oEmbed['author_name'];
        }
        return $metadata;
    }
    /**
     * @param File $file
     * @param bool $relativeToCurrentScript
     * @return string
     */
    public function getPublicUrl(File $file, $relativeToCurrentScript = false)
    {
        $mediaId = $this->getOnlineMediaId($file);
        return 'https://www.instagram.com/p/'. $mediaId  . '/';
    }
    /**
     * @param File $file
     * @return string
     */
    public function getPreviewImage(File $file)
    {
        $mediaId = $this->getOnlineMediaId($file);
        $temporaryFileName = $this->getTempFolderPath() . 'instagram_' . md5($mediaId) . '.jpg';
        if (!file_exists($temporaryFileName)) {
            $instagram = $this->getOEmbedData($mediaId);
            if (!empty($instagram['thumbnail_url'])) {
                $previewImage = GeneralUtility::getUrl($instagram['thumbnail_url']);
                if ($previewImage !== false) {
                    file_put_contents($temporaryFileName, $previewImage);
                    GeneralUtility::fixPermissions($temporaryFileName);
                }
            }
        }
        return $temporaryFileName;
    }

    /**
     * @param string $mediaId
     * @param string $format
     * @return string
     */
    public function getOEmbedUrl($mediaId, $format = 'json')
    {
        return 'https://api.instagram.com/oembed/?url=http://www.instagram.com/p/' . $mediaId . '/';
    }
}