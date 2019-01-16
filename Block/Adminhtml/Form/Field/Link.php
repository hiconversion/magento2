<?php

namespace Hic\Integration\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;

/**
 * Class HicLink
 * @package Hic\Integration\Block\Adminhtml\Form\Field
 */
class Link extends Field
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
     * Replace field markup with link button
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element) // @codingStandardsIgnoreLine
    {
        $title = __("Get Site ID");
        $urlId = 'text-groups-braintree-section-groups-braintree-groups-braintree-'
            . 'hic-groups-link-existing-fields-site-url-value';
        $emailId = 'text-groups-braintree-section-groups-braintree-groups-braintree-'
            . 'hic-groups-link-existing-fields-email-value';

        $storeId = 0;
        if ($this->getRequest()->getParam("website")) {
            $website = $this->_storeManager->getWebsite($this->getRequest()->getParam("website"));
            if ($website->getId()) {
                $storeId = $website->getId();
            }
        }
        $endpoint = $this->getUrl("hiconversion/configuration/LinkHiconversion", ['storeId' => $storeId]);
        $html = '<button type="button" title="' . $title . '" class="button" onclick="' .
            "linkHicAccount.call(this, '$endpoint', '$urlId', '$emailId')" .
            '"><span>' . $title . '</span></button>';
        return $html;
    }
}
