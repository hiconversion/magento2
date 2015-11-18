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

namespace Hic\Integration\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;

/**
 * UserData section source
 *
 * @category Hic
 * @package Integration
 * @author HiConversion <support@hiconversion.com>
 */
class UserData extends \Magento\Framework\Object implements SectionSourceInterface
{

    /**
     * @var \Hic\Integration\Helper\Helper
     */
    protected $helper;

    /**
     * \Hic\Integration\Helper\Helper $helper
     * array $data
     */
    public function __construct(
        \Hic\Integration\Helper\Helper $helper,
        array $data = []
    ) {
        parent::__construct($data);
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        $data = [];
        if ($this->helper->isEnabled()) {
            $user = $this->getUserData();
            if (null !== $user) {
                $data = $user;
            }
        } else {
            $data["disabled"] = true;
        }
        return $data;
    }

    /**
     * gets user data from helper
     *
     * @return object
     */
    protected function getUserData()
    {
        return $this->helper->getUserData();
    }
}
