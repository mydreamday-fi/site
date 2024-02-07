<?php

namespace Mydreamday\Custom\Block\Carousel;

use Magento\Catalog\Api\CategoryRepositoryInterface;

class NewProducts extends \Magento\Catalog\Block\Product\ListProduct {

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
        $productCollection = $this->collectionFactory->create();
        
		$productCollection
				->addMinimalPrice()
				->addFinalPrice()
				->addAttributeToSelect(['entity_id', 'url', 'name', 'small_image', 'visibility', 'price', 'sku', 'is_saleable'])
				->addUrlRewrite()
				->addAttributeToFilter('is_saleable', 1)
				->addAttributeToFilter('visibility', 4)
				->addAttributeToSort('entity_id','desc')
				->setPageSize(20);
		 
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
