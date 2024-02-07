<?php

namespace Tm\Subcategory\Plugin;

use Magento\Catalog\Model\Category\DataProvider as Subject;

class DataProviderPlugin
{
    protected $_storeManager;

    protected $_helper;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Tm\Subcategory\Helper\Data $helper
    ) {
        $this->_storeManager = $storeManager;
        $this->_helper = $helper;
    }

   
    public function aroundGetData(
        Subject $subject,
        \Closure $proceed
    ) {
        $result = $proceed();

        $category = $subject->getCurrentCategory();
        if ($category) {
            $categoryData = $category->getData();

            if (isset($categoryData[\Tm\Subcategory\Helper\Data::ATTRIBUTE_NAME])) {
                unset($categoryData[\Tm\Subcategory\Helper\Data::ATTRIBUTE_NAME]);

                $result[$category->getId()][\Tm\Subcategory\Helper\Data::ATTRIBUTE_NAME] = array(
                    array(
                        'name' => $category->getData(\Tm\Subcategory\Helper\Data::ATTRIBUTE_NAME),
                        'url' => $this->_helper->getImageUrl($category),
                    )
                );
            }
        }

        return $result;
    }
}