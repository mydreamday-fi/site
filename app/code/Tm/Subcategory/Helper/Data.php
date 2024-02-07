<?php

namespace Tm\Subcategory\Helper;

class Data
    extends \Magento\Framework\App\Helper\AbstractHelper
{
    const ATTRIBUTE_NAME = "subcategory_image";
    protected $_storeManager;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
    }

    public function getImageUrl(\Magento\Catalog\Model\Category $category)
{
		$image = $category->getData(self::ATTRIBUTE_NAME);
		
		if (filter_var($image, FILTER_VALIDATE_URL)) { // Check if $image is a valid URL
			return $image;
		} else {
			return $this->getUrl($image);
		}
	}

    public function getUrl($value)
    {
        $url = false;
        if ($value) {
            if (is_string($value)) {
                $url = $this->_storeManager->getStore()->getBaseUrl(
                        \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                    ) . 'catalog/category/' . $value;
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Something went wrong while getting the image url.')
                );
            }
        }
        
        return $url;
    }
}