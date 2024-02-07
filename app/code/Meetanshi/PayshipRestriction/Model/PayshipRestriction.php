<?php
namespace Meetanshi\PayshipRestriction\Model;
 
use Magento\Framework\Model\AbstractModel;
 
class PayshipRestriction extends AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Meetanshi\PayshipRestriction\Model\ResourceModel\PayshipRestriction');
    }
}
