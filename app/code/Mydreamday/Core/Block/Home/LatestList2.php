<?php

namespace Mydreamday\Core\Block\Home;

use Magento\Catalog\Api\CategoryRepositoryInterface;

class LatestList2 extends \Magento\Catalog\Block\Product\ListProduct {

    protected $categoryRepository;
    
    protected $_resource;
	
	protected $collectionFactory;
    
    public function __construct(
			\Magento\Catalog\Block\Product\Context $context,
			\Magento\Framework\Data\Helper\PostHelper $postDataHelper,
			\Magento\Catalog\Model\Layer\Resolver $layerResolver,
			CategoryRepositoryInterface $categoryRepository,
			\Magento\Framework\Url\Helper\Data $urlHelper,
			\Magento\Framework\App\ResourceConnection $resource,
			\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
			array $data = []
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->collectionFactory = $collectionFactory;
        $this->_resource = $resource;

        parent::__construct($context, $postDataHelper, $layerResolver, $categoryRepository, $urlHelper, $data);
    }

    protected function _getProductCollection() {
        return $this->getProducts();
    }
    
    public function getProducts() {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $store = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $stoteId =  $store->getStore()->getId();              
        $productCollection = $this->collectionFactory->create();
        
		$productCollection
				->addAttributeToSelect('*')
			//	->setPageSize(20) // fetching only 20 products
			//	->setOrder('entity_id','desc')
				->addAttributeToFilter('is_saleable', 1)
				->addAttributeToFilter('visibility', 4)
				->addAttributeToSort('entity_id','desc')
                ->addStoreFilter($store->getStore())
				->setPageSize(20);
			//	->setPage(1);
		 
        return $productCollection;
    }

    public function getLoadedProductCollection() {
        return $this->getProducts();
    }

    public function getProductCount() {
        $limit = $this->getData("product_count");
        if(!$limit)
            $limit = 10;
        return $limit;
    }
}
