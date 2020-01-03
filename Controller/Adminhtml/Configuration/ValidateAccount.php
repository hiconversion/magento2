<?php

namespace Hic\Integration\Controller\Adminhtml\Configuration;

use Hic\Integration\Model\Api;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class ValidateAccount
 * controller action for validate account button
 */
class ValidateAccount extends \Magento\Backend\App\Action
{

    /**
     * @var Api
     */
    private $hicApi;

    /**
     * ValidateAccount constructor.
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
        $siteId = $request->getParam("site_id");
        $response = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        try {
            $result = $this->hicApi->validateAccount($siteUrl, $email, $siteId);
            if (isset($result)) {
                $response->setData($result);
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
