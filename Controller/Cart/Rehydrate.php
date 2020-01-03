<?php

namespace Hic\Integration\Controller\Cart;

use Magento\Checkout\Model\Session;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Store\Model\Store;
use Magento\Quote\Api\GuestCartRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Controller\ResultFactory;
use Hic\Integration\Model\Data;

/**
 * Class Rehydrate
 * controller action for rehydrating a previously stored cart
 */
class Rehydrate extends Action
{

    const UTM_MEDIUM_PARAM = 'utm_medium';
    const UTM_MEDIUM_VALUE = 'email';

    const UTM_CAMPAIGN_PARAM = 'utm_campaign';
    const UTM_CAMPAIGN_VALUE = 'pp-cart-recovery';
    /**
     * cart query parameter to look for in URL
     */
    const CART_ID_PARAM = 'cartId';

     /**
     * Path to Magento's cart controller
     */
    const MAGENTO_CART_PATH = 'checkout/cart';

    /**
     * The array option key for scope in Magento's URLs
     */
    const MAGENTO_URL_OPTION_SCOPE = '_scope';

    /**
     * The array option key for URL type in Magento's URLs
     */
    const MAGENTO_URL_OPTION_LINK_TYPE = '_type';

    /**
     * The array option key for no session id in Magento's URLs.
     * The session id should be included into the URLs which are potentially
     * used during the same session, e.g. Oauth redirect URL. For example for
     * product URLs we cannot include the session id as the product URL should
     * be the same for all visitors.
     */
    const MAGENTO_URL_OPTION_NOSID = '_nosid';

    /**
     * The url type to be used for links.
     *
     * This is the only URL type that works correctly for URLs when
     * "Add Store Code to Urls" setting is set to "Yes"
     *
     * UrlInterface::URL_TYPE_WEB
     * - returns an URL without rewrites and without store codes
     *
     * UrlInterface::URL_TYPE_LINK
     * - returns an URL with rewrites and with store codes in URL (if
     * setting "Add Store Code to Urls" set to yes)
     *
     * UrlInterface::URL_TYPE_DIRECT_LINK
     * - returns an URL with rewrites but without store codes
     *
     * @see UrlInterface::URL_TYPE_LINK
     *
     * @var string
     */
    public static $urlType = UrlInterface::URL_TYPE_LINK;
    
    /**
     * @var Context
     */
    private $context;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var GuestCartRepositoryInterface
     */
    private $guestCartRepository;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var Data
     */
    private $hicModel;

    /**
     * Rehydrate constructor.
     *
     * @param Context  $context
     * @param Session  $checkoutSession
     * @param GuestCartRepositoryInterface $guestCartRepository
     * @param CartRepositoryInterface $cartRepository
     * @param Data $hicModel
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        GuestCartRepositoryInterface $guestCartRepository,
        CartRepositoryInterface $cartRepository,
        Data $hicModel
    ) {
        parent::__construct($context);
        $this->context = $context;
        $this->checkoutSession = $checkoutSession;
        $this->guestCartRepository = $guestCartRepository;
        $this->cartRepository = $cartRepository;
        $this->hicModel = $hicModel;
    }

    public function execute()
    {
        $store = $this->hicModel->getStore();
        $redirectUrl = $store->getBaseUrl();

        $url = $this->context->getUrl();
        $currentUrl = $url->getCurrentUrl();

        $cartId = $this->getRequest()->getParam(self::CART_ID_PARAM);
        try {
            if ($cartId) {
                $quote = null;
                try {
                    $quote = $this->guestCartRepository->get($cartId);
                } catch (\Exception $e) {
                    // guestCartRepository throws NoSuchEntityExceptions if cartId is not
                    // a masked id in the case of logins
                    if (!($e instanceof \Magento\Framework\Exception\NoSuchEntityException)) {
                        throw $e;
                    }
                }
                if ($quote === null || !$quote->getId()) {
                    // try and see if we are getting an actual cart id and not a masked one
                    $quote = $this->cartRepository->get($cartId);
                }
                if ($quote === null || !$quote->getId()) {
                    $this->setMissingCartMessage();
                } else {
                    if (!$quote->getIsActive()) {
                        $quote->setIsActive(true);
                        $this->cartRepository->save($quote);
                    }
                    $this->checkoutSession->setQuoteId($quote->getId());
                    $redirectUrl = $this->getCartUrl($store, $currentUrl);
                }
            } else {
                $this->setMissingCartMessage();
            }
        } catch (\Exception $e) {
            $this->setMissingCartMessage();
        }

        return $this->_redirect($redirectUrl);
    }

    private function setMissingCartMessage()
    {
        $this->messageManager->addError(__('Sorry, we could not find your cart'));
    }

    private function getCartUrl(Store $store, $currentUrl)
    {
        $zendHttp = \Zend_Uri_Http::fromString($currentUrl);
        $urlParameters = $zendHttp->getQueryAsArray();

        $urlParameters[self::UTM_MEDIUM_PARAM] = self::UTM_MEDIUM_VALUE;
        $urlParameters[self::UTM_CAMPAIGN_PARAM] = self::UTM_CAMPAIGN_VALUE;

        $defaultParams = $this->getUrlOptionsWithNoSid($store);
        $url = $store->getUrl(
            self::MAGENTO_CART_PATH,
            $defaultParams
        );

        if (!empty($urlParameters)) {
            foreach ($urlParameters as $key => $val) {
                $url = $this->replaceQueryParamInUrl(
                    $key,
                    $val,
                    $url
                );
            }
        }

        return $url;
    }

    /**
     * Returns the default options for fetching Magento urls with no session id
     *
     * @param Store $store
     * @return array
     */
    private function getUrlOptionsWithNoSid(Store $store)
    {
        $params = [
            self::MAGENTO_URL_OPTION_NOSID => true,
            self::MAGENTO_URL_OPTION_LINK_TYPE => self::$urlType,
            self::MAGENTO_URL_OPTION_SCOPE => $store->getCode(),
        ];
        return $params;
    }

    /**
     * Replaces or adds a query parameter to a url.
     *
     * @param string $param the query param name to replace.
     * @param mixed $value the query param value to replace.
     * @param string $url the url.
     * @return string the updated url.
     */
    private function replaceQueryParamInUrl($param, $value, $url)
    {
        $parsedUrl = parse_url($url);
        $queryString = isset($parsedUrl['query']) ? $parsedUrl['query'] : '';
        $queryString = $this->replaceQueryParam($param, $value, $queryString);
        $parsedUrl['query'] = $queryString;
        return $this->buildUrl($parsedUrl);
    }

    /**
     * Replaces a parameter in a query string with given value.
     *
     * @param string $param the query param name to replace.
     * @param mixed $value the query param value to replace.
     * @param string $queryString the query string.
     * @return string the updated query string.
     */
    private function replaceQueryParam($param, $value, $queryString)
    {
        $parsedQuery = $this->parseQueryString($queryString);
        $parsedQuery[$param] = $value;
        return http_build_query($parsedQuery);
    }

    /**
     * Parses the given query string and returns the parts as an assoc array.
     *
     * @see http://php.net/manual/en/function.parse-str.php
     * @param string $queryString the query string to parse.
     * @return array the parsed string as assoc array.
     */
    private function parseQueryString($queryString)
    {
        if (empty($queryString)) {
            return [];
        }
        parse_str($queryString, $parsedQueryString);
        return $parsedQueryString;
    }

    /**
     * Builds a url based on given parts.
     *
     * @see http://php.net/manual/en/function.parse-url.php
     * @param array $parts part(s) of an URL in form of a string or associative array like parseUrl() returns.
     * @return string
     */
    private function buildUrl(array $parts)
    {
        $scheme = isset($parts['scheme']) ? $parts['scheme'] . '://' : '';
        $host = isset($parts['host']) ? $parts['host'] : '';
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        $user = isset($parts['user']) ? $parts['user'] : '';
        $pass = isset($parts['pass']) ? ':' . $parts['pass'] : '';
        $pass = ($user || $pass) ? "$pass@" : '';
        $path = isset($parts['path']) ? $parts['path'] : '';
        $query = isset($parts['query']) ? '?' . $parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';
        return $scheme . $user . $pass . $host . $port . $path . $query . $fragment;
    }
}
