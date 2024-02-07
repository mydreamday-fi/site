<?php

namespace Mydreamday\Saldo\Block\Adminhtml;

class Index extends \Magento\Backend\Block\Template
{
    protected $_template = 'Mydreamday_Saldo::index.phtml';
    
    protected $productId;
    protected $productName;
    protected $qty;
	protected $shelfLocation;

    public function setProductId($productId)
    {
        $this->productId = $productId;
    }

    public function getProductId()
    {
        return $this->productId;
    }

    public function setProductName($productName)
    {
        $this->productName = $productName;
    }

    public function getProductName()
    {
        return $this->productName;
    }

    public function setQty($qty)
    {
        $this->qty = $qty;
    }

    public function getQty()
    {
        return $this->qty;
    }
	
	public function setShelfLocation($shelfLocation)
    {
        $this->shelfLocation = $shelfLocation;
    }
	
	public function getShelfLocation()
    {
        return $this->shelfLocation;
    }
}

