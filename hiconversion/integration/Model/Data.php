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

namespace Hic\Integration\Model;

/**
 * Integration data model
 *
 * @author HiConversion <support@hiconversion.com>
 */
class Data extends \Magento\Framework\Model\AbstractModel
{

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;
    
    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $catalogData;
 
    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $productHelper;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;
   
    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Sales\Model\Resource\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Resource\Category\Collection\Factory
     */
    protected $categoryCollectionFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Model\Resource\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Catalog\Model\Resource\Category\Collection\Factory $categoryCollectionFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\Resource\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Catalog\Model\Resource\Category\Collection\Factory $categoryCollectionFactory,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->request = $request;
        $this->catalogData = $catalogData;
        $this->productHelper = $productHelper;
        $this->productRepository = $productRepository;
        $this->cart = $cart;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Determines and returns page route
     *
     * @return string
     */
    protected function getRoute()
    {
        return $this->request->getFullActionName();
    }

    /**
     * Determines if its a product page or not
     *
     * @return boolean
     */
    public function isProduct()
    {
        return 'catalog_product_view' == $this->getRoute();
    }

     /**
     * Determines if Confirmation page or not
     *
     * @return boolean
     */
    public function isConfirmation()
    {
        return 'checkout_onepage_success' == $this->getRoute();
    }

     /**
     * Retrieves page route and breadcrumb info and populates page
     * attribute
     *
     * @return array $this
     */
    public function populatePageData()
    {
        $crumb = array();
        foreach ($this->catalogData->getBreadcrumbPath() as $item) {
            $crumb[] = $item['label'];
        }

        $this->setPage(
            array(
                'route' => $this->getRoute(),
                'bc' => $crumb
            )
        );
        return $this;
    }

     /**
     * Returns category names for each product
     * passed into function
     *
     * @param Magento\Catalog\Api\Data\ProductInterface $product
     * @return array $categoryNames
     */
    protected function getCategoryNames($product)
    {
        $catCollection = $this->categoryCollectionFactory->create()
            ->addIdFilter($product->getCategoryIds())
            ->addNameToResult()
            ->addIsActiveFilter();
        
        $categoryNames = array();
 
        foreach ($catCollection as $category) {
            $categoryNames[] = $category->getName();
        }
 
        return $categoryNames;
        
    }

     /**
     * Returns product information for each cart
     * item passed into function
     *
     * @param array $items
     * @params boolean $isOrder
     * @return array $data
     */
    protected function getCartItems($items, $isOrder)
    {
        $data = array();
          
        // build list of product IDs from either cart or transaction object.
        $productIds = array();
        foreach ($items as $item) {
            $productIds[] = $item->getProduct()->getId();
        }

        $filters = [];
        $filters[] = $this->filterBuilder
            ->setField('entity_id')
            ->setConditionType('in')
            ->setValue($productIds)
            ->create();
        
        $this->searchCriteriaBuilder->addFilters($filters);

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchResults = $this->productRepository->getList($searchCriteria);
        $products = $searchResults->getItems();
 
        $count = 0;
        foreach ($products as $product) {
            $info = array();
            $info['ds'] = (float)$items[$count]->getDiscountAmount();
            $info['tx'] = (float)$items[$count]->getTaxAmount();
            $info['pr'] = (float)$items[$count]->getRowTotalInclTax();
            $info['bpr'] = (float)$items[$count]->getPrice();
            if ($isOrder) {
                $info['qt'] = (float)$items[$count]->getQtyOrdered();
            } else {
                $info['qt'] = (float)$items[$count]->getQty();
            }
            
            $info['desc'] = strip_tags($product->getDescription());
            $info['id'] = $product->getId();
            $info['url'] = $product->getProductUrl();
            $info['nm'] = $product->getName();
            $info['img'] = $this->productHelper->getImageUrl($product);
            $info['sku'] = $product->getSku();
            $info['cat'] = $this->getCategoryNames($product);
            $data[] = $info;
            $count = $count + 1;
        }
        return $data;
       
    }

     /**
     * Retrieves all orders for a given customer id
     *
     * @param int $customerId
     * @return \Magento\Sales\Api\Data\OrderInterface[] Array of items
     */
    protected function getOrders($customerId)
    {
        return $this->orderCollectionFactory->create()
            ->addAttributeToFilter('customer_id', $customerId);
    }

    /**
     * Retrieves product information and populates product attribute
     *
     * @return array $this
     */
    public function populateProductData()
    {
        $currentProduct = $this->catalogData->getProduct();
        if ($currentProduct) {
            $data['cat'] = $this->getCategoryNames($currentProduct);
            $data['id']  = $currentProduct->getId();
            $data['nm']  = $currentProduct->getName();
            $data['url'] = $this->productHelper->getProductUrl($currentProduct);
            $data['sku'] = $currentProduct->getSku();
            $data['bpr'] = $currentProduct->getPrice();
            $data['img'] = $this->productHelper->getImageUrl($currentProduct);
            $this->setProduct($data);
        }
        return $this;
    }

    /**
     * Retrieves cart information and populates cart attribute
     *
     * @return array $this
     */
    public function populateCartData()
    {
        $cartQuote = $this->cart->getQuote();
  
        $data = array();
        if ($cartQuote->getSubtotal()) {
            $data['st'] = (float)$cartQuote->getSubtotal();
        }
        if ($cartQuote->getGrandTotal()) {
            $data['tt'] = (float)$cartQuote->getGrandTotal();
        }
        if ($cartQuote->getItemsCount()) {
            $data['qt'] = (float)$cartQuote->getItemsQty();
        }
        if ($cartQuote->getStoreCurrencyCode()) {
            $data['cu'] = $cartQuote->getStoreCurrencyCode();
        }
        $data['li'] = $this
            ->getCartItems($cartQuote->getAllVisibleItems(), false);
        $this->setCart($data);
          
        return $this;
    }

    
    /**
     * Retrieves user information and populates user attribute
     *
     * @return array $this
     */
    public function populateUserData()
    {
        $data = array();
        $data['auth'] = $this->customerSession->isLoggedIn();
        $data['ht'] = false;
        $data['nv'] = true;
        $data['cg'] = $this->customerSession->getCustomerGroupId();
        $customerId = $this->customerSession->getId();
        if ($customerId) {
            $customer = $this->customerRepository->getById($customerId);
            if ($customer) {
                $orders = $this->getOrders($customerId);
                if ($orders) {
                    $data['ht'] = $orders->getSize() > 0;
                }
                if ($customer->getDob()) {
                    $data['bday'] = $customer->getDob();
                }
                if ($customer->getGender()) {
                    $data['gndr'] = $customer->getGender();
                }
                if ($customer->getEmail()) {
                    $data['email'] = $customer->getEmail();
                }
                $data['id'] = $customer->getId();
                $data['nv'] = false;
                $data['nm'] = trim($customer->getFirstname()) . ' ' . trim($customer->getLastname());
                $data['since'] = $customer->getCreatedAt();
            }
        }
        $this->setUser($data);
        
        return $this;
    }

    /**
     * Retrieves order information and populates tr attribute
     *
     * @return array $this
     */
    public function populateOrderData()
    {
        $order = $this->checkoutSession->getLastRealOrder();
        
        if ($order) {
            if ($order->getIncrementId()) {
                $transaction['id'] = $order->getIncrementId();
            }
            if ($order->getOrderCurrencyCode()) {
                $transaction['cu'] = $order->getOrderCurrencyCode();
            }
            if ($order->getSubtotal()) {
                $transaction['st'] = (float)$order->getSubtotal();
            }
            if ($order->getTaxAmount()) {
                $transaction['tx'] = (float)$order->getTaxAmount();
            }
            if ($order->getPayment()->getMethodInstance()->getTitle()) {
                $transaction['type'] = $order->getPayment()->getMethodInstance()->getTitle();
            }
            if ($order->getGrandTotal()) {
                $transaction['tt'] = (float)$order->getGrandTotal();
            }
            if ($order->getTotalQtyOrdered()) {
                $transaction['qt'] = (float)$order->getTotalQtyOrdered();
            }
            if ($order->getCouponCode()) {
                $transaction['coup'] = array($order->getCouponCode());
            }
            if ($order->getDiscountAmount() > 0) {
                $transaction['ds'] = -1 * $order->getDiscountAmount();
            }
            $transaction['li'] = $this
                ->getCartItems($order->getAllVisibleItems(), true);
            $transaction['sh'] = (float)$order->getShippingAmount();
            $transaction['shm'] = $order->getShippingMethod()
                ? $order->getShippingMethod() : '';
            $this->setTr($transaction);
        }
        return $this;
    }
}
