<?php

namespace Hic\Integration\Model;

class Data extends \Magento\Framework\Model\AbstractModel
{ 
    protected $request;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Catalog\Helper\Data $catalogData
    ) {
        $this->request = $request;
        $this->catalogData = $catalogData;
    }

    protected function _getRoute()
    {
        return $this->request->getActionName();
    }


    public function isProduct()
    {
        return 'catalog_product_view' == $this->_getRoute();
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

}
