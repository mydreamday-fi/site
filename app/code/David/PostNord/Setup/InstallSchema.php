<?php

namespace David\PostNord\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * Upgrades DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $orderTable = 'sales_order';
        
        $setup->getConnection()->addColumn(
            $setup->getTable($orderTable),
            'postnord_data',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '',
                'comment' =>'postnord data'
            ]
        );
        $setup->getConnection()->addColumn(
            $setup->getTable($orderTable),
            'postnord_refund_data',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '',
                'comment' =>'postnord refund data'
            ]
        );
        $setup->getConnection()->addColumn(
            $setup->getTable($orderTable),
            'postnord_pickup',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '',
                'comment' =>'postnord pickup method'
            ]
        );

        $setup->endSetup();
    }
}
