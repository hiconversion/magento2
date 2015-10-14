<?php

namespace Hic\Integration\Model;

class Data extends \Magento\Framework\Model\AbstractModel
{ 
  
    protected $logger; 
  
    protected $request;

    protected $catalogData;

    protected $productHelper;

    protected $productRepository;

    protected $cartHelper;

    protected $searchCriteriaBuilder;
   
    protected $filterBuilder;

    protected $customerRepository;

    protected $customerSession;

    protected $orderCollectionFactory;

    protected $categoryCollectionFactory;

    protected $checkoutSession;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\Resource\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Catalog\Model\Resource\Category\Collection\Factory $categoryCollectionFactory,
        \Magento\Checkout\Model\Session $checkoutSession
   ) {
        $this->logger = $logger;
        $this->request = $request;
        $this->catalogData = $catalogData;
        $this->productHelper = $productHelper;
        $this->productRepository = $productRepository;
        $this->cartHelper = $cartHelper;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->checkoutSession = $checkoutSession;
    }

    protected function _getRoute()
    {
        return $this->request->getFullActionName();
    }


    public function isProduct()
    {
        return 'catalog_product_view' == $this->_getRoute();
    }

    public function isConfirmation()
    {
        return 'checkout_onepage_success' == $this->_getRoute();
    }

    public function populatePageData()
    {
        $crumb = array();
        foreach ($this->catalogData->getBreadcrumbPath() as $item) {
          $crumb[] = $item['label'];
        }

        $this->setPage(
            array(
                'route' => $this->_getRoute(),
                'bc' => $crumb
            )
        );
        return $this;
    }

    protected function _getCategoryNames($product)
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

    protected function _getCartItems($items, $isOrder)
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
            //TODO: figure out how to merge this data in so I can create a separate
            // function to handle getting general production info
            $info['desc'] = strip_tags($product->getDescription());
            $info['id'] = $product->getId();
            $info['url'] = $product->getProductUrl();
            $info['nm'] = $product->getName();
            $info['img'] = $this->productHelper->getImageUrl($product);
            $info['sku'] = $product->getSku();
            $info['cat'] = $this->_getCategoryNames($product);
            $data[] = $info;
            $count = $count + 1;
        }
        return $data;
       
    }

    //TODO: we may need to limit this further but this is one to one with 1.x magento extension
    protected function _getOrders($customerId)
    {
        return $this->orderCollectionFactory->create()
            ->addAttributeToFilter('customer_id', $customerId);
    }

    public function populateProductData()
    {
        $currentProduct = $this->catalogData->getProduct();
        if ($currentProduct) {
            $data['cat'] = $this->_getCategoryNames($currentProduct);
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

    public function populateCartData()
    {
        $cartQuote = $this->cartHelper->getQuote();
        if ($cartQuote->getItemsCount() > 0) {
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
                ->_getCartItems($cartQuote->getAllVisibleItems(), false);
            $this->setCart($data);
                
        }
        return $this;
    }

    

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
	        $orders = $this->_getOrders($customerId);
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
            if ($order->getCouponCode()) {
                $transaction['coup'] = array($order->getCouponCode());
            }
            if ($order->getDiscountAmount() > 0) {
                $transaction['ds'] = -1 * $order->getDiscountAmount();
            }
            $transaction['li'] = $this
                ->_getCartItems($order->getAllVisibleItems(), false);
            $transaction['sh'] = (float)$order->getShippingAmount();
            $transaction['shm'] = $order->getShippingMethod()
                ? $order->getShippingMethod() : '';
            $this->setTr($transaction);
        }
        return $this;
    }
}
