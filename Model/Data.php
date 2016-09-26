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

use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Checkout\Model\Session as CheckoutSession;

/**
 * Integration data model
 *
 * @author HiConversion <support@hiconversion.com>
 */
class Data extends \Magento\Framework\Model\AbstractModel
{

    /**
     * @var RequestInterface
     */
    private $request;
    
    /**
     * @var CatalogHelper
     */
    private $catalogData;
 
    /**
     * @var Product
     */
    private $productHelper;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
   
    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @param RequestInterface $request
     * @param CatalogHelper $catalogData
     * @param Product $productHelper
     * @param ProductRepositoryInterface $productRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerSession $customerSession
     * @param OrderRepositoryInterface $orderRepository
     * @param CategoryRepositoryInterface $categoryRepository
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        RequestInterface $request,
        CatalogHelper $catalogData,
        Product $productHelper,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        CustomerRepositoryInterface $customerRepository,
        CustomerSession $customerSession,
        OrderRepositoryInterface $orderRepository,
        CategoryRepositoryInterface $categoryRepository,
        CheckoutSession $checkoutSession
    ) {
        $this->request = $request;
        $this->catalogData = $catalogData;
        $this->productHelper = $productHelper;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
        $this->orderRepository = $orderRepository;
        $this->categoryRepository = $categoryRepository;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Determines and returns page route
     *
     * @return string
     */
    private function getRoute()
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
     * @return $this
     */
    public function populatePageData()
    {
        $crumb = [];
        foreach ($this->catalogData->getBreadcrumbPath() as $item) {
            $crumb[] = $item['label'];
        }

        $this->setPage(
            [
                'route' => $this->getRoute(),
                'bc' => $crumb
            ]
        );
        return $this;
    }

     /**
     * Returns category names for each product
     * passed into function
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return array $categoryNames
     */
    private function getCategoryNames($product)
    {
        
        $categoryNames = [];
        foreach ($product->getCategoryIds() as $categoryId) {
            $category = $this->categoryRepository->get($categoryId);
            array_push($categoryNames, $category->getName());
        }
 
        return $categoryNames;
    }

     /**
     * Returns product information for each cart
     * item passed into function
     *
     * @param array $items
     * @param boolean $isOrder
     * @return array $data
     */
    private function getCartItems($items, $isOrder)
    {
        $data = [];
          
        // build list of product IDs from either cart or transaction object.
        $productIds = [];
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
            $info = [];
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
            $info['url'] = $this->productHelper->getProductUrl($product);
            $info['nm'] = $product->getName();
            $info['img'] = $this->productHelper->getImageUrl($product);
            $info['sku'] = $product->getSku();
            $info['cat'] = $this->getCategoryNames($product);
            $data[] = $info;
            $count ++;
        }
        return $data;
    }

     /**
     * Retrieves all orders for a given customer id
     *
     * @param int $customerId
     * @return \Magento\Sales\Api\Data\OrderInterface[] Array of items
     */
    private function getOrders($customerId)
    {
        $this->searchCriteriaBuilder->addFilter('customer_id', $customerId);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchResults = $this->orderRepository->getList($searchCriteria);
        return $searchResults->getItems();
    }

    /**
     * Retrieves product information and populates product attribute
     *
     * @return $this
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
     * @return $this
     */
    public function populateCartData()
    {
        $cartQuote = $this->checkoutSession->getQuote();
  
        $data = [];
        $data['st'] = (float)$cartQuote->getSubtotal();
        $data['tt'] = (float)$cartQuote->getGrandTotal();
        $data['qt'] = (float)$cartQuote->getItemsQty();
        $data['cu'] = $cartQuote->getStoreCurrencyCode();
        $data['li'] = $this
            ->getCartItems($cartQuote->getAllVisibleItems(), false);
        $this->setCart($data);
          
        return $this;
    }
    
    /**
     * Retrieves user information and populates user attribute
     *
     * @return $this
     */
    public function populateUserData()
    {
        $data = [];
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
                    $data['ht'] = count($orders) > 0;
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
     * @return $this
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
                $transaction['coup'] = [$order->getCouponCode()];
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
