<?php

namespace Hic\Integration\Controller\Adminhtml\Configuration;

use Hic\Integration\Model\Api;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class LinkAccount
 * controller action for link account button
 */
class LinkAccount extends \Magento\Backend\App\Action
{

    /**
     * @var Api
     */
    private $hicApi;

    /**
     * LinkAccount constructor.
     * @param Action\Context $context
     * @param Api $hicApi
     */
    public function __construct(
        Action\Context $context,
        Api $hicApi
    ) {
        parent::__construct($context);
        $this->hicApi = $hicApi;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $request = $this->getRequest();
        $siteUrl = $request->getParam("site_url");
        $email = $request->getParam("email");
        $response = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        try {
            $siteId = $this->hicApi->getSiteId($siteUrl, $email);
            if (isset($siteId)) {
                $response->setData($siteId);
                $response->setHttpResponseCode(200);
            } else {
                $response->setHttpResponseCode(404);
            }
        } catch (\Exception $e) {
            $response->setHttpResponseCode(400);
        }
        return $response;
    }
}
