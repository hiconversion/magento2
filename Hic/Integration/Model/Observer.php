<?php
namespace Hic\Integration\Model;

class Observer
{
    private $logger;

    public function __construct (
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }


    public function beforeResponse(\Magento\Framework\Event\Observer $observer)
    {
        echo "hiconversionbeforeresponse";
        $this->logger->addDebug('inside beforeResponse');
    }


    public function interceptResponse(\Magento\Framework\Event\Observer $observer)
    { 
        echo "hiconversion afterhtml"; 
        $this->logger->addDebug('inside interceptResponse');        
    }
}
