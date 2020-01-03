<?php

namespace Hic\Integration\Controller\Adminhtml\Configuration;

use Hic\Integration\Model\Api;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class ActivateAccount
 * controller action for activate account button
 */
class ActivateAccount extends \Magento\Backend\App\Action
{

    /**
     * @var Api
     */
    private $hicApi;

    /**
     * ActivateAccount constructor.
     *
     * @param Action\Context  $context
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
        $pw = $request->getParam("password");
        $response = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        try {
            $result = $this->hicApi->activateAccount($siteUrl, $email, $pw);

            if (isset($result) && isset($result['result']) && $result['result'] === "success"
                && isset($result['external'])) {
                $response->setData($result);
                $response->setHttpResponseCode(200);
            } else {
                $response->setData($result);
                $response->setHttpResponseCode(400);
            }
        } catch (\Exception $e) {
            $response->setHttpResponseCode(400);
        }
        return $response;
    }
}
