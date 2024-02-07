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
 * @copyright  Copyright (c) 2017-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomShippingMethod\Api\Data;

/**
 * Interface CustomMethodInterface
 * @package Bss\CustomShippingMethod\Api\Data
 */
interface CustomMethodStoreInterface extends CustomMethodInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const STORE_ID = "store_id";

    /**
     * Set store id
     *
     * @param string $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Get store id
     *
     * @return string
     */
    public function getStoreId();
}
