<?php

namespace Mydreamday\Custom\Plugin;

use Magento\Quote\Model\Quote as MagentoQuote;

class QuotePlugin
{
    public function afterBeforeSave(MagentoQuote $subject, $result): MagentoQuote
    {
        if ($result->getCustomerId()) {
            $result->setCustomerIsGuest(false);
        }

        return $result;
    }
}