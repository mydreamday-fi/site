<?php

namespace Meetanshi\PayshipRestriction\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Meetanshi\PayshipRestriction\Helper\Data;

class ActivePaymentMethod implements ObserverInterface
{
    protected $helper;

    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    public function execute(EventObserver $observer)
    {
        
        $result = $observer->getResult();
        $method = $observer->getMethodInstance()->getCode();
        
        if (!$this->helper->canUseMethod($method, 'payment')) {
            $result->setData('is_available', false);
        }
    }
}
