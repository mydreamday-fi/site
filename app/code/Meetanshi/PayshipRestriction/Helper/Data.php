<?php

namespace Meetanshi\PayshipRestriction\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Meetanshi\PayshipRestriction\Model\ResourceModel\PayshipRestriction\CollectionFactory;
use Magento\Payment\Model\Config;
use Magento\Shipping\Model\Config as shippingConfig;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory as customerGroupCollection;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\Helper\AbstractHelper;
use Meetanshi\PayshipRestriction\Model\Customer\Context as CustomerContext;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 * @package Meetanshi\PayshipRestriction\Helper
 */
class Data extends AbstractHelper
{
    /**
     * @var null
     */
    protected static $customerGroupId = null;
    /**
     * @var StoreManagerInterface|null
     */
    protected $storeManager = null;
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    /**
     * @var Config
     */
    protected $paymentConfig;
    /**
     * @var shippingConfig
     */
    protected $shippingConfig;
    /**
     * @var customerGroupCollection
     */
    protected $customerGroupCollection;
    /**
     * @var Session
     */
    protected $customerSession;
    /**
     * @var HttpContext
     */
    protected $httpContext;

    /**
     * Data constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory $collectionFactory
     * @param Config $paymentConfig
     * @param shippingConfig $shippingConfig
     * @param customerGroupCollection $customerGroupCollection
     * @param Session $customerSession
     * @param HttpContext $httpContext
     */
    public function __construct(Context $context, StoreManagerInterface $storeManager, CollectionFactory $collectionFactory, Config $paymentConfig, shippingConfig $shippingConfig, customerGroupCollection $customerGroupCollection, Session $customerSession, HttpContext $httpContext)
    {
        $this->storeManager = $storeManager;
        $this->collectionFactory = $collectionFactory;
        $this->paymentConfig = $paymentConfig;
        $this->shippingConfig = $shippingConfig;
        $this->customerGroupCollection = $customerGroupCollection;
        $this->customerSession = $customerSession;
        $this->httpContext = $httpContext;
        parent::__construct($context);
    }

    /**
     * @return mixed
     */
    public function getMethodsVisibility($type, $websiteId, $method = null)
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('type', ['eq' => $type]);
        if ($method !== null) {
            $collection->addFieldToFilter('method', ['eq' => $method]);
        }
        $collection->addFieldToFilter('website_id', ['eq' => $websiteId]);
        return $collection->load();
    }

    /**
     * @return array
     */
    public function getActivePaymentMethods()
    {
        return $this->paymentConfig->getActiveMethods();
    }

    /**
     * @return AbstractCarrierInterface[]
     */
    public function getActiveShippingMethods()
    {
        return $this->shippingConfig->getActiveCarriers();
    }

    /**
     * @return Collection
     */
    public function getCustomerGroup()
    {
        return $this->customerGroupCollection->create();
    }
    
    /**
     * @return bool
     */
    public function canUseMethod($method, $type)
    {
        if (strpos($method, "klarna_pay") !== false) {
            $method = "klarna_kp";
        }
        
        if ($this->isEnabled() == 0) {
            return true;
        }
        if ($type == 'payment') {
            return $this->_canUsePaymentMethod($method);
        }
        if ($type == 'shipping') {
            return $this->_canUseShippingMethod($method);
        }
        return true;
    }
    
    /**
     * @return bool
     */
    public function _canUseShippingMethod($method)
    {
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $type = 'shipping';
        $flag = false;
        $collection = $this->getMethodsVisibility($type, $websiteId, $method);

        foreach ($collection as $methods) {
            if ($methods->getRestrictionId()) {
                if ($methods->getCustomerGroupIds() != '') {
                    $allowedGroups = explode(',', $methods->getCustomerGroupIds());
                    if (in_array($this->_getCustomerGroupId(), $allowedGroups)) {
                        $flag = true;
                    } else {
                        $flag = false;
                    }
                } else {
                    $flag = false;
                }
            } else {
                $flag = true;
            }
        }

        if ($flag) {
            return true;
        }

        return false;
    }
    
    /**
     * @return bool
     */
    protected function _canUsePaymentMethod($method)
    {
        
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $type = 'payment';
        $flag = false;
        $collection = $this->getMethodsVisibility($type, $websiteId, $method);

        foreach ($collection as $methods) {
            if ($methods->getRestrictionId()) {
                if ($methods->getCustomerGroupIds() != '') {
                    $allowedGroups = explode(',', $methods->getCustomerGroupIds());
                    if (in_array($this->_getCustomerGroupId(), $allowedGroups)) {
                        $flag = true;
                    } else {
                        $flag = false;
                    }
                } else {
                    $flag = false;
                }
            } else {
                $flag = true;
            }
        }

        if ($flag) {
            return true;
        }

        return false;
    }

    /**
     * @return int|null
     */
    protected function _getCustomerGroupId()
    {
        $groupId = (int)$this->httpContext->getValue(CustomerContext::CONTEXT_CUSTOMER_ID);
        if ($groupId == 0) {
            $customerSession = $this->customerSession;

            if (!(self::$customerGroupId === null)) {
                return self::$customerGroupId;
            }
            if (!$customerSession->getId()) {
                return 0;
            }
            $groupId = $customerSession->getCustomerGroupId();
        }
        return $groupId;
    }

    /**
     * @return mixed
     */
    public function isEnabled()
    {
        return $this->scopeConfig->getValue('psrestrict/general/enabled', ScopeInterface::SCOPE_STORE);
    }
}
