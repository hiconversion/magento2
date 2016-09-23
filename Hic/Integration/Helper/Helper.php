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

namespace Hic\Integration\Helper;

/**
 * Integration data helper
 *
 * @author HiConversion <support@hiconversion.com>
 */
class Helper extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * enabled id in configuration
     */
    const SETTINGS_ENABLED = 'hiconversion/configuration/enabled';

    /**
     * site ID in configuration
     */
    const SETTINGS_SITE_ID = 'hiconversion/configuration/site_id';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Hic\Integration\Model\Data
     */
    private $hicModel;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Hic\Integration\Model\Data $hicModel
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Hic\Integration\Model\Data $hicModel
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->hicModel = $hicModel;
    }
  
    /**
     * Returns Site ID from Configuration
     *
     * @return string
     */
    public function getSiteId()
    {
        return $this->scopeConfig->getValue(self::SETTINGS_SITE_ID);
    }

    /**
     * Determines if module is enabled or not
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->scopeConfig->getValue(self::SETTINGS_ENABLED);
    }

    /**
     * Returns Data that can be cached relative to a page
     * currently page and product data
     * @return object
     */
    public function getPageData()
    {
        if ($this->hicModel->isProduct()) {
            $this->hicModel->populateProductData();
        }

        $this->hicModel->populatePageData();

        return $this->hicModel;
    }
    
    /**
     * Returns Cart Data
     * @return object
     */
    public function getCartData()
    {
        $this->hicModel->populateCartData();

        return $this->hicModel->getData('cart');
    }

    /**
     * Returns user data
     * @return object
     */
    public function getUserData()
    {
        $this->hicModel->populateUserData();

        return $this->hicModel->getData('user');
    }

    /**
     * Returns order data
     * @return object
     */
    public function getOrderData()
    {
        if ($this->hicModel->isConfirmation()) {
            $this->hicModel->populateOrderData();
        }

        return $this->hicModel;
    }
}
