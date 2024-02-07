<?php

namespace Mydreamday\Custom\Plugin\Carrier;

use Magento\Quote\Api\Data\ShippingMethodExtensionFactory;

/**
 * Class Description
 * 
 */
class Description
{
    /**
     * @var ShippingMethodExtensionFactory
     */
    protected $extensionFactory;

    /**
     * Description constructor.
     * @param ShippingMethodExtensionFactory $extensionFactory
     */
    public function __construct(
        ShippingMethodExtensionFactory $extensionFactory
    )
    {
        $this->extensionFactory = $extensionFactory;
    }

    /**
     * @param $subject
     * @param $result
     * @param $rateModel
     * @return mixed
     */
    public function afterModelToDataObject($subject, $result, $rateModel)
    {
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $customshipping = $objectManager->create('Bss\CustomShippingMethod\Model\Carrier')->getCollectionMethod();
     $notice=[];
    foreach ($customshipping as $k => $value) {
        if (isset($value['shipping_notice'])) { // Check if 'shipping_notice' key exists
            $notice[] = $value['shipping_notice'];
        }
    }
        $extensionAttribute = $result->getExtensionAttributes() ?
            $result->getExtensionAttributes()
            :
            $this->extensionFactory->create()
        ;
       
        $extensionAttribute->setShippingNotice($notice);
       
        $result->setExtensionAttributes($extensionAttribute);
    
        return $result;
    }
}