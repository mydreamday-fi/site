<?php

namespace Meetanshi\PayshipRestriction\Plugin;

use Magento\Payment\Block\Form\Container;
use Meetanshi\PayshipRestriction\Helper\Data;

/**
 * Class Methods
 */
class Methods
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
     * @return array
     */

    public function afterGetMethods(Container $subject, $methods)
    {
        foreach ($methods as $key => $method) {
            if (!$this->helper->canUseMethod($method->getCode(), 'payment')) {
                unset($methods[$key]);
            }
        }

        return $methods;
    }
}
