<?php
namespace David\PostNord\Plugin\Quote\Address;

class Rate
{
    public function afterImportShippingRate($subject, $result, $rate)
    {
        if ($rate instanceof \Magento\Quote\Model\Quote\Address\RateResult\Method) {
            $result->setDistance(
                $rate->getDistance()
            );
            $result->setAddress(
                $rate->getAddress()
            );
            $result->setRawDistance(
                $rate->getRawDistance()
            );
        }

        return $result;
    }
}