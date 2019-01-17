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
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\OnlineMedia\Helpers\AbstractOEmbedHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TwitterTweetHelper extends AbstractOEmbedHelper
{
    /**
     * @param string $url
     * @param \TYPO3\CMS\Core\Resource\Folder $targetFolder
     * @return File
     */
    public function transformUrlToFile($url, Folder $targetFolder)
    {
        $tweetId = null;
        // Try to get the Twitter code from given url.
        // These formats are supported with and without http(s)://
        // - facebook.com/<site>/videos/<code> # Share URL
        if (preg_match('/twitter\.com\/.*\/status\/*([0-9]+)/i', $url, $matches)) {
            $tweetId = $matches[1];
        }
        if (empty($tweetId)) {
            return null;
        }
        return $this->transformMediaIdToFile($tweetId, $targetFolder, $this->extension);
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
            if (!empty($oEmbed) && isset($oEmbed['author_name'])) {
                $fileName = $oEmbed['author_name'] . '_' . $mediaId . '.' . $fileExtension;
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
            if (empty($file->getProperty('title')) && isset($oEmbed['author_name'])) {
                $metadata['title'] = strip_tags($oEmbed['author_name']);
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
        $oEmbed = $this->getOEmbedData($this->getOnlineMediaId($file));
        if ($oEmbed) {
            return $oEmbed['url'];
        }
        return '';
    }
    /**
     * @param File $file
     * @return string
     */
    public function getPreviewImage(File $file)
    {
        return GeneralUtility::getFileAbsFileName(
            'EXT:onlinemediahandler/Resources/Public/Icons/twitter.jpg'
        );
    }
    /**
     * @param string $mediaId
     * @param string $format
     * @return string
     */
    public function getOEmbedUrl($mediaId, $format = 'json')
    {
        return 'https://api.twitter.com/1.1/statuses/oembed.json?id=' . $mediaId;
    }
}
