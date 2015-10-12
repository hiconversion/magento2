<?php

namespace Hic\Integration\Plugin;

class Plugin
{
    private $logger;

    private $objectManager;

    public function __construct (
        \Psr\Log\LoggerInterface $logger,
         \Magento\Framework\ObjectManagerInterface $objectManager        
    ) {
        $this->logger = $logger;
        $this->objectManager = $objectManager;
    }



    public function afterRenderHeadContent(\Magento\Framework\View\Page\Config\Renderer $subject, $html)
    {
        $this->logger->addDebug('rendering head content ');
        $tagPage = $this->objectManager->create('Hic\Integration\Block\Tag')
            ->setTemplate('headPage.phtml')
            ->toHtml();

        $this->logger->addDebug('tagPage-' . $tagPage);
  
       return $tagPage . $html;
       return $html;
    }
}
