<?php

namespace Hic\Integration\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;

/**
 * Class Activation
 * provides activation button for admin panel
 */
class Activation extends Field
{
    /**
     * Force scope label to be blank
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _renderScopeLabel(\Magento\Framework\Data\Form\Element\AbstractElement $element) // @codingStandardsIgnoreLine
    {
        return '';
    }

    /**
     * Replace field markup with activation button
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element) // @codingStandardsIgnoreLine
    {
        $title = __("Create HiConversion Account");
        $storeId = 0;

        if ($this->getRequest()->getParam("website")) {
            $website = $this->_storeManager->getWebsite($this->getRequest()->getParam("website"));
            if ($website->getId()) {
                $storeId = $website->getId();
            }
        }

        $endpoint = $this->getUrl("hiconversion/configuration/activateAccount", ['storeId' => $storeId]);
        $html = '<button type="button" title="' . $title . '" class="button" onclick="' .
            "activateHicAccount.call(this, '$endpoint')" .
            '"><span>' . $title . '</span></button>';

        return $html;
    }
}
