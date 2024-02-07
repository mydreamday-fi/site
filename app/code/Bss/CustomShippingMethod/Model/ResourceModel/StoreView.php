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
namespace Bss\CustomShippingMethod\Model\ResourceModel;

/**
 * Class StoreView
 *
 * @package Bss\CustomShippingMethod\Model\ResourceModel
 */
class StoreView extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('bss_custom_shipping_method_store', null);
    }

    /**
     * Save $storeArray .
     *
     * @param array| $storeArray
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveDB($storeArray)
    {
        $connection = $this->getConnection();
        $table = $this->getMainTable();
        $connection->insert($table, $storeArray);
    }

    /**
     * Delete $methodId.
     *
     * @param int| $method_id
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteDB($method_id)
    {
        $connection = $this->getConnection();
        $table = $this->getMainTable();
        $connection->delete($table, "method_id = $method_id");
    }

    /**
     * Select $methodId.
     *
     * @param int| $method_id
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function selectDB($method_id)
    {
        $select = $this->getConnection()->select()->from(
            $this->getMainTable(),
            ['store_id']
        )->where(
            'method_id = ?',
            $method_id
        );
        return $this->getConnection()->fetchCol($select);
    }

    public function getMethodIds($storeId)
    {
        $data = [];
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getMainTable(),
            ['method_id']
        )->where(
            'store_id = ?',
            $storeId
        );
        if ($storeId) {
            $select->orWhere('store_id = 0');
        }
        $query = $connection->query($select);
        while ($row = $query->fetch()) {
            array_push($data, $row["method_id"]);
        }
        return $data;
    }
}
