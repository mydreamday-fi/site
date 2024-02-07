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
namespace Bss\CustomShippingMethod\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 *
 * @package Bss\CustomShippingMethod\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * Install
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $table = $installer->getConnection()->newTable(
            $installer->getTable('bss_custom_shipping_method')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true]
        )->addColumn(
            'enabled',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false]
        )->addColumn(
            'name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'name'
        )->addColumn(
            'type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'type'
        )->addColumn(
            'price',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            null,
            ['scale'=>4,'precision'=>12],
            'price'
        )->addColumn(
            'calculate_handling_fee',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            "Calculate Handling Fee "
        )->addColumn(
            'handling_fee',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            null,
            ['scale'=>4,'precision'=>12],
            'Handling Fee'
        )->addColumn(
            'applicable_countries',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false],
            'Applicable Countries'
        )->addColumn(
            'specific_countries',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'Specific Countries'
        )->addColumn(
            'minimum_order_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            null,
            ['scale'=>4,'precision'=>12],
            'Minimum Order Amount'
        )->addColumn(
            'maximum_order_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            null,
            ['scale'=>4,'precision'=>12],
            'Maximum Order amount'
        )->addColumn(
            'sort_order',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [],
            'Sort Order'
        )->addIndex(
            $installer->getIdxName('bss_custom_shipping_method', ['id']),
            ['id']
        )->setComment(
            'Bss Custom shipping Method'
        );
        $installer->getConnection()->createTable($table);
        /**
         * Create
         */
        $table = $this->table2($installer);
        $installer->getConnection()->createTable($table);
        $installer->endSetup();
    }

    /**
     * Create Table2.
     *
     * @param SchemaSetupInterface $installer
     * @return mixed
     * @throws \Zend_Db_Exception
     */
    public function table2($installer)
    {
        return $installer->getConnection()->newTable(
            $installer->getTable('bss_custom_shipping_method_store')
        )->addColumn(
            'method_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'ID Custom Shipping Method '
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false],
            'ID Store'
        )->addIndex(
            $installer->getIdxName('bss_custom_shipping_method_store', ['store_id']),
            ['store_id']
        )->addIndex(
            $installer->getIdxName('bss_custom_shipping_method_store', ['method_id']),
            ['method_id']
        )->addForeignKey(
            $installer->getFkName(
                'bss_custom_shipping_method_store',
                'store_id',
                'store',
                'store_id'
            ),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName(
                'bss_custom_shipping_method_store',
                'method_id',
                'bss_custom_shipping_method',
                'id'
            ),
            'method_id',
            $installer->getTable('bss_custom_shipping_method'),
            'id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
    }
}
