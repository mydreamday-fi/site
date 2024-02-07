<?php
namespace Tm\Subcategory\Plugin;

use Magento\Catalog\Model\Category as Subject;

class CategoryPlugin
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
        \Closure $proceed,
        $key = '',
        $index = null
    ) {
        if ($key == \Tm\Subcategory\Helper\Data::ATTRIBUTE_NAME) {
            $result = $proceed($key, $index);
            if ($result) {
                return $this->_helper->getUrl($result);
            } else {
                return $result;
            }
        }

        return $proceed($key, $index);
    }
}