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

namespace Hic\Integration\Model;

class HicConfig extends \Magento\Paypal\Model\PayflowConfig
{

    /**
     * site ID in configuration
     */
    const SETTINGS_SITE_ID = 'hiconversion/configuration/bn_code';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_logger;

    /**
     * Returns BN Code from Configuration
     *
     * @return string
     */
    public function getBNCode()
    {
        return $this->scopeConfig->getValue(self::SETTINGS_SITE_ID, ScopeInterface::SCOPE_STORE);
    }


    /**
     * BN code getter
     *
     * @return string
     */
    public function getBuildNotationCode()
    {
        $newBnCode = $this->getBNCode();
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $logger = $om->get('\Psr\Log\LoggerInterface');
        $logger->info('newBnCode'.$newBnCode);
        if (empty($newBnCode)) {
            $newBnCode = parent::getBuildNotationCode();
        }
        return $newBnCode;
    }
}