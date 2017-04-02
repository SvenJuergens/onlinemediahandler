<?php
namespace SvenJuergens\Onlinemediahandler\Resource\Rendering;

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
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\Rendering\FileRendererInterface;
use TYPO3\CMS\Core\Resource\OnlineMedia\Helpers\OnlineMediaHelperInterface;
use TYPO3\CMS\Core\Resource\OnlineMedia\Helpers\OnlineMediaHelperRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;


class SoundcloudRenderer implements FileRendererInterface {
    /**
     * @var OnlineMediaHelperInterface
     */
    protected $onlineMediaHelper;
    /**
     * @return integer
     */
    public function getPriority()
    {
        return 1;
    }
    /**
     * @param FileInterface $file
     * @return boolean
     */
    public function canRender(FileInterface $file)
    {
        return ($file->getMimeType() === 'audio/soundcloud' || $file->getExtension() === 'soundcloud') && $this->getOnlineMediaHelper($file) !== false;
    }
    /**
     * Get online media helper
     *
     * @param FileInterface $file
     * @return bool|OnlineMediaHelperInterface
     */
    protected function getOnlineMediaHelper(FileInterface $file)
    {
        if ($this->onlineMediaHelper === null) {
            $orgFile = $file;
            if ($orgFile instanceof FileReference) {
                $orgFile = $orgFile->getOriginalFile();
            }
            if ($orgFile instanceof File) {
                $this->onlineMediaHelper = OnlineMediaHelperRegistry::getInstance()->getOnlineMediaHelper($orgFile);
            } else {
                $this->onlineMediaHelper = false;
            }
        }
        return $this->onlineMediaHelper;
    }
    /**
     * @param FileInterface $file
     * @param int|string $width
     * @param int|string $height
     * @param array $options
     * @param bool $usedPathsRelativeToCurrentScript
     * @return string
     */
    public function render(FileInterface $file, $width, $height, array $options = [], $usedPathsRelativeToCurrentScript = false)
    {
        if ($file instanceof FileReference) {
            $orgFile = $file->getOriginalFile();
        } else {
            $orgFile = $file;
        }

        $soundPath = $this->getOnlineMediaHelper($file)->getOnlineMediaId($orgFile);
        $oembedData = $this->getOEmbedData($soundPath);
        return $oembedData['html'];
    }

    public function getOEmbedData($mediaId)
    {
        $soundURl = 'https://soundcloud.com' . $mediaId;
        $soundcloudOEmbed =  'https://soundcloud.com/oembed?url=' . urlencode($soundURl) . '&format=json' ;
        $oEmbed = GeneralUtility::getUrl(
            $soundcloudOEmbed
        );
        if ($oEmbed) {
            $oEmbed = json_decode($oEmbed, true);
        }
        return $oEmbed;
    }
}