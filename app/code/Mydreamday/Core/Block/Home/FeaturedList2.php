<?php

namespace Mydreamday\Core\Block\Home;

use Magento\Catalog\Api\CategoryRepositoryInterface;

class FeaturedList2 extends \Magento\Catalog\Block\Product\ListProduct {

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
        $this->_resource = $resource;
		$this->collectionFactory = $collectionFactory;

        parent::__construct($context, $postDataHelper, $layerResolver, $categoryRepository, $urlHelper, $data);
    }

    protected function _getProductCollection() {
        return $this->getProducts();
    }
    
    public function getProducts() {
        $count = $this->getProductCount();
        $productCollection = $this->collectionFactory->create();

		$productCollection
				->addMinimalPrice()
				->addFinalPrice()
				->addAttributeToSelect('name')
				->addAttributeToSelect('image')
				->addAttributeToSelect('small_image')
				->addAttributeToSelect('thumbnail')
				->addAttributeToSelect('is_saleable')
				->addAttributeToSelect('sw_featured')
				->addUrlRewrite()
				->addAttributeToFilter('is_saleable', 1, 'left')
				->addAttributeToFilter('sw_featured', 1, 'left');

        $productCollection->getSelect()
            ->orderRand()
            ->limit($count);

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
