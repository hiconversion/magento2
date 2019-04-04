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

use Magento\Paypal\Model\AbstractConfig;
use Hic\Integration\Helper\Helper;

/**
 * Plugin for setting BN Code
 *
 * @author HiConversion <support@hiconversion.com>
 */
class HicPayPalAbstractConfigPlugin
{
  
   /**
    * @var Data
    */
    private $hicHelper;

    /**
     * @param Helper $hicHelper
     */
    public function __construct(
        Helper $hicHelper
    ) {
        $this->hicHelper = $hicHelper;
    }

    /**
     * BN code getter
     *
     * @return string
     */
    public function afterGetBuildNotationCode(AbstractConfig $subject, $result)
    {
        $newBnCode = $this->hicHelper->getBNCode();
        if (!empty($newBnCode)) {
            $result = $newBnCode;
        }
        return $result;
    }
}
