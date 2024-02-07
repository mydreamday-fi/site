<?php
namespace Meetanshi\PayshipRestriction\Model\ResourceModel;
 
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
 
class PayshipRestriction extends AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('payment_shipping_restriction', 'restriction_id');
    }
}
