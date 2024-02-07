<?php

namespace David\PostNord\Plugin\Carrier;

use Magento\Quote\Model\Cart\ShippingMethodConverter;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Api\Data\ShippingMethodExtensionFactory;
use Magento\Framework\Registry;


class Additional
{
    /**
     * @var ShippingMethodExtensionFactory
     */
    protected $extensionFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * DeliveryDate constructor.
     *
     * @param ShippingMethodExtensionFactory $extensionFactory
     * @param Registry $registry
     */
    public function __construct(
      ShippingMethodExtensionFactory $extensionFactory,
      Registry $registry
    )
    {
      $this->extensionFactory = $extensionFactory;
      $this->registry = $registry;
    }

    /**
     * Add delivery date information to the carrier data object
     *
     * @param ShippingMethodConverter $subject
     * @param ShippingMethodInterface $result
     * @return ShippingMethodInterface
     */
    public function afterModelToDataObject(ShippingMethodConverter $subject, ShippingMethodInterface $result)
    {
        $customdata = $this->registry->registry('postnord_customdata');
        $method = $result->getMethodCode();
        if(isset($customdata[$method])){
            $extensionAttribute = $result->getExtensionAttributes() ?
                $result->getExtensionAttributes()
                :
                $this->extensionFactory->create()
            ;
            $extensionAttribute->setDistance($customdata[$method]['distance']);
            $extensionAttribute->setRawDistance($customdata[$method]['rawDistance']);
            $extensionAttribute->setAddress($customdata[$method]['address']);
            $result->setExtensionAttributes($extensionAttribute); 
        }
        return $result;
    }
}
