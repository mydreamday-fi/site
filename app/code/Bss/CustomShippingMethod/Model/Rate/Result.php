<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CustomShippingMethod
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomShippingMethod\Model\Rate;

/**
 * Class Result
 *
 * @package Bss\CustomShippingMethod\Model\Rate
 */
class Result extends \Magento\Shipping\Model\Rate\Result
{
    /**
     * Sort Rates By Price
     *
     * @return $this
     */
    public function sortRatesByPrice()
    {
        return $this;
    }
}
