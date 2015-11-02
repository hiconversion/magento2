<?php

namespace Hic\Integration\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;


class CartData extends \Magento\Framework\Object implements SectionSourceInterface
{

    protected $helper;
   
    protected $logger;
   
    public function __construct(
        \Hic\Integration\Helper\Helper $helper,
        \Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
        parent::__construct($data);
        $this->helper = $helper;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {   
        $data = [];     
        if ($this->helper->isEnabled()) {   
            $cart = $this->getCartData();
            if (null !== $cart) {
                $data = $cart;
            }
        } else {
            $data["disabled"] = true;
        }
        return $data;
    }

    protected function getCartData()
    {
        return $this->helper->getCartData();
    }

}