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
* @category Hic
* @package Hic_Integration
* @Copyright © 2015 HiConversion, Inc. All rights reserved.
* @license [http://opensource.org/licenses/MIT] MIT License
*/

namespace Hic\Integration\Plugin;

/**
* Plugin for injecting head content at top of head
*
* @category Hic
* @package Integration
* @author HiConversion <support@hiconversion.com>
*/
class Plugin
{
  
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $templateName
     * @return string
     **/
    protected function getBlockHtml($templateName)
    {
        return $this->objectManager->create('Hic\Integration\Block\Tag')
            ->setTemplate($templateName)
            ->toHtml();
    }

    /**
     * @param \Magento\Framework\View\Page\Config\Renderer $subject
     * @param string $html
     * @return string
     */
    public function afterRenderHeadContent(\Magento\Framework\View\Page\Config\Renderer $subject, $html)
    {
        $tagAlways = $this->getBlockHtml('headAlways.phtml');
  
        $tagPage = $this->getBlockHtml('headPage.phtml');
     
        $tagNever = $this->getBlockHtml('headNever.phtml');

        return $tagAlways . $tagPage . $tagNever . $html;
    }
}
