<?php

$onlineMediaHandler = [
    'facebook' => [
        'helper' => \SvenJuergens\Onlinemediahandler\Resource\OnlineMedia\Helpers\FacebookVideoHelper::class,
        'renderer' => \SvenJuergens\Onlinemediahandler\Resource\Rendering\FacebookVideoRenderer::class,
        'mimeType' => 'video/facebook'
    ],
    'twitter' => [
        'helper' => \SvenJuergens\Onlinemediahandler\Resource\OnlineMedia\Helpers\TwitterTweetHelper::class,
        'renderer' => \SvenJuergens\Onlinemediahandler\Resource\Rendering\TwitterTweetRenderer::class,
        'mimeType' => 'tweet/twitter'
    ],
    'soundcloud' => [
        'helper' => \SvenJuergens\Onlinemediahandler\Resource\OnlineMedia\Helpers\SoundcloudHelper::class,
        'renderer' => \SvenJuergens\Onlinemediahandler\Resource\Rendering\SoundcloudRenderer::class,
        'mimeType' => 'audio/soundcloud'
    ],
    'instagram' => [
        'helper' => \SvenJuergens\Onlinemediahandler\Resource\OnlineMedia\Helpers\InstagramHelper::class,
        'renderer' => \SvenJuergens\Onlinemediahandler\Resource\Rendering\InstragramRenderer::class,
        'mimeType' => 'external/instagram'
    ]
];

$rendererRegistry = \TYPO3\CMS\Core\Resource\Rendering\RendererRegistry::getInstance();

foreach ($onlineMediaHandler as $identifier => $requirements) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['onlineMediaHelpers'][$identifier] = $requirements['helper'];
    $rendererRegistry->registerRendererClass($requirements['renderer']);
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['FileInfo']['fileExtensionToMimeType'][$identifier] =
        $requirements['mimeType'];
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['mediafile_ext'] .= ',' . $identifier;
}
