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

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Plugin for setting Braintree BN Code
 *
 * @author HiConversion <support@hiconversion.com>
 */
class HicChannelDataPlugin
{
  
   /**
     * bn code in configuration
     */
    const SETTINGS_BN_CODE = 'hiconversion/configuration/bn_code';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ProductMetadataInterface $productMetadata
     * @param Config $config
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig; 
    }

    /**
     * Returns BN Code from Configuration
     *
     * @return string
     */
    public function getBNCode()
    {
        return $this->scopeConfig->getValue(self::SETTINGS_BN_CODE, ScopeInterface::SCOPE_STORE);
    }


    /**
     * BN code getter
     *
     * @return string
     */
    public function afterBuild($buildSubject, $result)
    {
        $newBnCode = $this->getBNCode();
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $logger = $om->get('\Psr\Log\LoggerInterface');
        $logger->info('BraintTreeOverride newBnCode'.$newBnCode);
        if (!empty($newBnCode)) {
            $result = [
                'channel' => $newBnCode
            ];
            $logger->info('BrainTreeOverride setting new result'. print_r($result));
        } else {
            $logger->info('BraintTreeOverride falling back to default'. print_r($result));
        }
        return $result;
    }
}
