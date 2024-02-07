<?php

namespace Mydreamday\BusinessPayment\Plugin\Model;

class Purchaseorder
{
    public function aroundvalidate($subject, $proceed)
    {
        // check PoNumber is empty or not
        if (empty($subject->getInfoInstance()->getPoNumber())) {
            return $this;
        }
        return $this;
    }
}