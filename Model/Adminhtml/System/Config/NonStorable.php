<?php

namespace Hic\Integration\Model\Adminhtml\System\Config;

use Magento\Framework\App\Config\Value;

/**
 * Class NonStorable
 * Provides an admin form field that doesn't get stored in backend (for purposes of signup and such)
 */
class NonStorable extends Value
{
    public function beforeSave()
    {
        $this->setValue();
        parent::beforeSave();
    }
}
