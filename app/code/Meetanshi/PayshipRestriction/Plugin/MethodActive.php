<?php

namespace Meetanshi\PayshipRestriction\Plugin;

use Meetanshi\PayshipRestriction\Helper\Data;
use Magento\Paypal\Model\AbstractConfig as PaypalConfig;

/**
 * Class MethodActive
 */
class MethodActive
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
     * @return bool
     */
    public function afterIsMethodActive(PaypalConfig $subject, $result, $method = null)
    {
        if (!$this->helper->canUseMethod($method, 'payment')) {
            return false;
        }
        return $result;
    }
}
