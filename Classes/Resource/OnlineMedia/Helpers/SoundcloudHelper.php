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

class SoundcloudHelper extends AbstractOEmbedHelper
{
    /**
     * @param string $url
     * @param \TYPO3\CMS\Core\Resource\Folder $targetFolder
     * @return File
     */
    public function transformUrlToFile($url, Folder $targetFolder)
    {
        $soundPath = null;
        // Try to get the SoundCloud IF code from given Iframe or Wordpress Code
        // see "Share" Button on soundcloud.com
        //https://soundcloud.com/migosatl/bad-and-boujee-feat-lil-uzi-vert-prod-by-metro-boomin
        $data = parse_url($url);
        if ($data['host'] === 'soundcloud.com') {
            $soundPath = '/' . trim($data['path'], '/') . '/';
        }

        if (empty($soundPath)) {
            return null;
        }
        return $this->transformMediaIdToFile($soundPath, $targetFolder, $this->extension);
    }
    /**{
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
            if (empty($file->getProperty('title')) && isset($oEmbed['title'])) {
                $metadata['title'] = strip_tags($oEmbed['title']);
            }
            if (empty($file->getProperty('description')) && isset($oEmbed['description'])) {
                $metadata['description'] = strip_tags($oEmbed['description']);
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
        return 'https://soundcloud.com' . $this->getOnlineMediaId($file);
    }
    /**
     * @param File $file
     * @return string
     */
    public function getPreviewImage(File $file)
    {
        $soundPath = $this->getOnlineMediaId($file);
        $temporaryFileName = $this->getTempFolderPath() . 'soundcloud_' . md5($soundPath) . '.jpg';
        if (!file_exists($temporaryFileName)) {
            $soundInformation = $this->getOEmbedData($soundPath);
            if (!empty($soundInformation['thumbnail_url'])) {
                $previewImage = GeneralUtility::getUrl($soundInformation['thumbnail_url']);
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
        $soundURl = 'https://soundcloud.com' . $mediaId;
        return 'https://soundcloud.com/oembed?url=' . urlencode($soundURl) . '&format=json';
    }
}
