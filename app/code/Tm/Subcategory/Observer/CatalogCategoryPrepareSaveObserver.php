<?php

namespace Tm\Subcategory\Observer;

class CatalogCategoryPrepareSaveObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $category = $observer->getEvent()->getCategory();
        $category->setData($this->_postData($category->getData()));
    }

    protected function _postData(array $rawData)
    {
        $data = $rawData;
        $attributeName = \Tm\Subcategory\Helper\Data::ATTRIBUTE_NAME;
		if (empty($data[$attributeName])) {
            unset($data[$attributeName]);
            $data[$attributeName]['delete'] = true;
        }

        if (isset($data[$attributeName])&& is_array($data[$attributeName])
        ) {
            if (!empty($data[$attributeName]['delete'])) {
                $data[$attributeName] = null;
            } else {
                if (isset($data[$attributeName][0]['name'])&& isset($data[$attributeName][0]['tmp_name'])) {
                    $data[$attributeName] = $data[$attributeName][0]['name'];
                } else {
                    unset($data[$attributeName]);
                }
            }
        }

        return $data;
    }
}