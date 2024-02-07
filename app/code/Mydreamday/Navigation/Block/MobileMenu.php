<?php

namespace Mydreamday\Navigation\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session;
use Magento\Theme\Block\Html\Topmenu;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class MobileMenu extends \Magento\Framework\View\Element\Template
{
    protected $_customerSession;

     /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    public function __construct(
        Context $context,
        Session $customerSession,
        Topmenu $topMenu,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_customerSession = $customerSession;
        $this->_topmenu = $topMenu;
        $this->scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
    }

    public function isLoggedIn()
    {
        return $this->_customerSession->isLoggedIn();
    }

    /**
     * Retrieve config value by path and scope
     *
     * @param string $path
     * @param string $scopeType
     * @param null|string $scopeCode
     * @return mixed
     */
    public function getConfigValue($path)
    {
        $storeId = $this->_storeManager->getStore()->getId(); // Get the current store ID
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getMenuHtml()
    {
        $columnsLimit = $this->_topmenu->getColumnsLimit() ?: 0;
        $_menuHtml = $this->_topmenu->getHtml('mdd-level-top', 'mdd-menu-submenu', $columnsLimit);
        return $_menuHtml;
    }

    public function getCustomerName()
    {
        $fullName = $this->_customerSession->getCustomer()->getName();
        if (empty($fullName)) {
            return "";
        }
        $fullNameArray = explode(' ', $fullName);
        return $fullNameArray[0];
    }
	
	public function getCurrentStoreId()
	{
		return $this->_storeManager->getStore()->getId();
	}
}
