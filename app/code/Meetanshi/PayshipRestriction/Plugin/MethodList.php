<?php

namespace Meetanshi\PayshipRestriction\Plugin;

use Magento\Payment\Model\MethodList as PaymentMethodList;
use Meetanshi\PayshipRestriction\Helper\Data;

/**
 * Class MethodList
 */
class MethodList
{
    /**
     * @var Data
     */
    protected $helper;
    
    /**
     * @param Data $helper
     */
    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }
    
    /**
     * @return mixed
     */
    public function afterGetAvailableMethods(PaymentMethodList $subject, $availableMethods)
    {
        
        foreach ($availableMethods as $key => $method) {
            if (!$this->helper->canUseMethod($method->getCode(), 'payment')) {
                unset($availableMethods[$key]);
            }
        }

        return $availableMethods;
    }
}
