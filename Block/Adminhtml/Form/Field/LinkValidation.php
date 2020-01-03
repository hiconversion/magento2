<?php

namespace Hic\Integration\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;

/**
 * Class LinkValidation
 * Create link validation button for admin panel
 */
class LinkValidation extends Field
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

    private function getButtonHtml($fnName, $title, $endpoint)
    {
        return '<button type="button" style="margin-right:10px" title="' . $title . '" class="button" onclick="' .
        $fnName . ".call(this, '$endpoint')" .
        '"><span>' . $title . '</span></button>';
    }

    /**
     * Replace field markup with link button
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element) // @codingStandardsIgnoreLine
    {
        $linkTitle = __("Get Site ID");
        $validateTitle = __("Validate");
        
        $storeId = 0;
        if ($this->getRequest()->getParam("website")) {
            $website = $this->_storeManager->getWebsite($this->getRequest()->getParam("website"));
            if ($website->getId()) {
                $storeId = $website->getId();
            }
        }
        $linkEndpoint = $this->getUrl("hiconversion/configuration/LinkAccount", ['storeId' => $storeId]);
        $validateEndpoint = $this->getUrl("hiconversion/configuration/ValidateAccount", ['storeId' => $storeId]);

        $linkButton = $this->getButtonHtml('linkHicAccount', $linkTitle, $linkEndpoint);
        $validateButton = $this->getButtonHtml('validateHicAccount', $validateTitle, $validateEndpoint);
        
        $html = $linkButton . $validateButton;

        return $html;
    }
}
