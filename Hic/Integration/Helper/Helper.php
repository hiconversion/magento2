<?php

namespace Hic\Integration\Helper;


class Helper extends \Magento\Framework\App\Helper\AbstractHelper
{
    const SETTINGS_ENABLED = 'hiconversion/configuration/enabled';
    const SETTINGS_SITE_ID = 'hiconversion/configuration/site_id';


    protected $scopeConfig;

    protected $hicModel;


    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Hic\Integration\Model\Data $hicModel,
        \Magento\Framework\ObjectManagerInterface $objectManager

    ) {
        $this->scopeConfig = $scopeConfig;
        $this->hicModel = $hicModel;
    }
  
    public function getSiteId()
    {
        return $this->scopeConfig->getValue(self::SETTINGS_SITE_ID);
    }

    public function isEnabled()
    {
        return $this->scopeConfig->getValue(self::SETTINGS_ENABLED);
    }

    public function getPageData()
    {
        if ($this->hicModel->isProduct()) {
            $this->hicModel->populateProductData();
        }

        $this->hicModel->populatePageData();

        return $this->hicModel;
    }
    
    public function getCartData()
    {
        $this->hicModel->populateCartData();

        return $this->hicModel->getData('cart');
    }

    public function getUserData()
    {
        $this->hicModel->populateUserData();

        return $this->hicModel->getData('user');
    }

    public function getOrderData()
    {
        if ($this->hicModel->isConfirmation()) { 
            $this->hicModel->populateOrderData();
        }
            

        return $this->hicModel;
    }
}
