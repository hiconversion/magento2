<?php

namespace Hic\Integration\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;


class HicData extends \Magento\Framework\Object implements SectionSourceInterface
{

    protected $helper;
   
    protected $logger;

    protected $sessionData;
   
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
        $cart = $this->getCartData();
        if (null !== $cart) {
            $data['cart'] = $cart;
        }
        $user = $this->getUserData(); 
        if (null !== $user) {
            $data['user'] = $user;
        }
        return $data;
    }

    protected function getSessionData()
    {
        if (null === $this->sessionData) {
            $this->sessionData = $this->helper->hicSessionData();
        }
        return $this->sessionData;
    }

    protected function getSessionDataItem($name)
    {
        $sessionData = $this->getSessionData();
        return $sessionData->getData($name);
    }

    protected function getCartData()
    {
        return $this->getSessionDataItem('cart');
    }

    protected function getUserData()
    {
        return $this->getSessionDataItem('user');
    }

}
