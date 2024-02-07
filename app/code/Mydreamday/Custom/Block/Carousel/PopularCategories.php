<?php

namespace Mydreamday\Custom\Block\Carousel;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Store\Model\StoreManagerInterface;

class PopularCategories extends Template
{
    protected $categoryRepository;
    protected $storeManager;

    public function __construct(
        Template\Context $context,
        CategoryRepository $categoryRepository,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
    }

    public function getCategoryUrlById($categoryId)
    {
        try {
            $category = $this->categoryRepository->get($categoryId, $this->_storeManager->getStore()->getId());
            return $category->getUrl();
        } catch (\Exception $e) {
            return '#'; // Return a default value in case of an exception
        }
    }
}
