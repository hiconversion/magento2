<?php
/**
 * HiConversion
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * [http://opensource.org/licenses/MIT]
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @Copyright © 2015 HiConversion, Inc. All rights reserved.
 * @license [http://opensource.org/licenses/MIT] MIT License
 */

namespace Hic\Integration\Plugin;

/**
 * Plugin for injecting head content at top of head
 *
 * @author HiConversion <support@hiconversion.com>
 */
class HicPlugin
{
  
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;
    private $_logger;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->objectManager = $objectManager;
        $this->_logger = $logger;
    }

    /**
     * @param string $templateName
     * @return string
     **/
    protected function getBlockHtml($templateName)
    {
        $this->_logger->addDebug('setting templates');
        return $this->objectManager->create('\Hic\Integration\Block\Tag')
            ->setTemplate('Hic_Integration::' . $templateName)
            ->toHtml();
    }

    /**
     * @param \Magento\Framework\View\Page\Config\Renderer $subject
     * @param string $html
     * @return string
     */
    public function afterRenderHeadContent(\Magento\Framework\View\Page\Config\Renderer $subject, $html)
    {
        $this->_logger->addDebug('before rendering blocks');

        $tagAlways = $this->getBlockHtml('headAlways.phtml');

        $tagPage = $this->getBlockHtml('headPage.phtml');

        $tagNever = $this->getBlockHtml('headNever.phtml');

       // $this->_logger->addDebug('in afterRenderHeadContent' . $tagAlways . $tagPage . $tagNever);


        return $tagAlways . $tagPage . $tagNever . $html;
    }
}
