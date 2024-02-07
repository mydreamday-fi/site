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

use Bss\CustomShippingMethod\Api\Data\CustomMethodStoreInterface;
use Bss\CustomShippingMethod\Api\Data\CustomMethodInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Interface CustomShippingMethodRepositoryInterface
 */
interface CustomMethodRepositoryInterface
{
    /**
     * Get list custom method
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Bss\CustomShippingMethod\Api\CustomMethodSearchResultsInterface|\Magento\Framework\Api\SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Get list custom method: id, name, enable in, sort order
     *
     * @return mixed|array
     */
    public function getListCustomShipping();

    /**
     * Get custom method by id
     *
     * @param int $id
     * @return \Bss\CustomShippingMethod\Api\Data\CustomMethodStoreInterface|null
     */
    public function getById($id);

    /**
     * Get list custom shipping method by store id
     *
     * @param int $storeId
     * @return \Bss\CustomShippingMethod\Api\Data\CustomMethodInterface[]
     */
    public function getListCustomShippingStore($storeId);

    /**
     * Delete custom shipping method by id
     *
     * @param int $id
     * @return bool
     */
    public function delete($id);

    /**
     * Save custom shipping method
     *
     * @param \Bss\CustomShippingMethod\Api\Data\CustomMethodStoreInterface $customMethod
     * @return \Bss\CustomShippingMethod\Api\Data\CustomMethodStoreInterface
     * @throws CouldNotSaveException
     */
    public function save($customMethod);
}
