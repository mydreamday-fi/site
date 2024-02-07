<?php

namespace Meetanshi\PayshipRestriction\Plugin;

use Meetanshi\PayshipRestriction\Helper\Data;
use Magento\Quote\Model\Quote\Address;

/**
 * Class ShippingRates
 */
class ShippingRates
{
    /**
     * @var Data
     */
    protected $helper;
    
    /**
     * ShippingRates constructor.
     * @param Data $helper
     */
    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }
    
    /**
     * @return array
     */

    public function afterGetGroupedAllShippingRates(Address $subject, $rates)
    {
        foreach ($rates as $code => $method) {
            if (!$this->helper->canUseMethod($code, 'shipping')) {
                unset($rates[$code]);
            }
        }

        return $rates;
    }
}
