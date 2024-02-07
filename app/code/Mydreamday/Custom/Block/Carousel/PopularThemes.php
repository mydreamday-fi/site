<?php

namespace Mydreamday\Custom\Block\Carousel;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\CategoryFactory;
use Tm\Subcategory\Helper\Data as SubcategoryHelper;

class PopularThemes extends Template
{
    protected $_categoryFactory;
    protected $_helper;

    public function __construct(
        Template\Context $context,
        CategoryFactory $categoryFactory,
        SubcategoryHelper $helper, // Tm\Subcategory helper for fetching images
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_categoryFactory = $categoryFactory;
        $this->_helper = $helper;
    }

    public function getSubcategories($categoryId)
    {
		// Manually enter the IDs of the categories you want to display
		$featuredCategoryIds = [707, 246, 857, 761, 231, 761, 440, 212, 19];
	
        $category = $this->_categoryFactory->create()->load($categoryId);
        $collection = $this->_categoryFactory->create()->getCollection()
			->addAttributeToSelect(['entity_id', 'name', 'image', 'subcategory_image'])
			->addAttributeToFilter('is_active', 1)
			->addAttributeToFilter('entity_id', ['in' => $featuredCategoryIds])
			->setOrder('position', 'ASC')
			->addIdFilter($category->getChildren());

        return $collection;
    }

    public function getCategoryThumbImage($category)
    {
        return $this->_helper->getImageUrl($category);
    }
	
	public function getCategoryById($categoryId)
    {
        return $this->_categoryFactory->create()->load($categoryId);
    }
}
