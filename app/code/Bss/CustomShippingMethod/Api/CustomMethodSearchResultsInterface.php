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
namespace Bss\CustomShippingMethod\Api;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface CustomShippingMethodSearchResultsInterface
 */
interface CustomMethodSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get items
     *
     * @return \Bss\CustomShippingMethod\Api\Data\CustomMethodInterface[]
     */
    public function getItems();

    /**
     * Set items
     *
     * @param \Bss\CustomShippingMethod\Api\Data\CustomMethodInterface[] $items
     * @return \Bss\CustomShippingMethod\Api\CustomMethodSearchResultsInterface
     */
    public function setItems(array $items);
}
