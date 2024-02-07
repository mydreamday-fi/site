<?php

namespace Meetanshi\PayshipRestriction\Block\Adminhtml\PayshipRestriction;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\App\Action\Context as appContext;
use Magento\Framework\Registry;
use Meetanshi\PayshipRestriction\Helper\Data;
use Magento\Store\Model\ScopeInterface;

/**
 * Class PayshipRestriction
 * @package Meetanshi\PayshipRestriction\Block\Adminhtml\PayshipRestriction
 */
class PayshipRestriction extends Template
{
    /**
     * @var string
     */
    protected $type = '';
    /**
     * @var array
     */
    protected $visibility = [];
    /**
     * @var RequestInterface
     */
    protected $request;
    /**
     * @var Data
     */
    protected $dataHelper;
    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * PayshipRestriction constructor.
     * @param Context $context
     * @param appContext $appContext
     * @param Registry $registry
     * @param Data $dataHelper
     * @param array $data
     */
    public function __construct(Context $context, appContext $appContext, Registry $registry, Data $dataHelper, array $data = [])
    {
        $this->type = $appContext->getRequest()->getActionName();
        $this->request = $appContext->getRequest();
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
        $this->dataHelper = $dataHelper;
        $this->_prepareVisibility();
    }

    /**
     *
     */
    protected function _prepareVisibility()
    {
        $collection = $this->dataHelper->getMethodsVisibility($this->type, $this->getCurrentWebsite());
        foreach ($collection as $method) {
            $this->visibility[$method->getMethod()] = explode(',', $method->getCustomerGroupIds());
        }
    }

    /**
     * @return array
     */
    public function getMethods()
    {
        if ('payment' == $this->type) {
            $methods = $this->_getPaymentMethods();
        } elseif ('shipping' == $this->type) {
            $methods = $this->_getShippingMethods();
        }
        return $methods;
    }

    /**
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', ['_current' => 'true']);
    }

    /**
     * @param null $website
     * @return string
     */
    public function getWebsiteUrl($website = null)
    {
        if ($website === null) {
            $websiteId = 1;
        } else {
            $websiteId = $website->getId();
        }
        return $this->getUrl('*/*/*', ['website_id' => $websiteId, '_current' => true]);
    }

    /**
     * @return WebsiteInterface[]
     */
    public function getWebsites()
    {
        $websites = $this->_storeManager->getWebsites();
        return $websites;
    }

    /**
     * @return mixed
     */
    public function getCurrentWebsite()
    {
        $websiteId = $this->request->getParam('website_id', 1);
        return $websiteId;
    }

    /**
     * @return array
     */
    public function getCustomerGroups()
    {
        $groups = $this->dataHelper->getCustomerGroup();

        foreach ($groups as $eachGroup) {
            $option['value'] = $eachGroup->getCustomerGroupId();
            $option['label'] = $eachGroup->getCustomerGroupCode();
            $options[] = $option;
        }
        return $options;
    }

    /**
     * @param $group
     * @param $methodCode
     * @return bool
     */
    public function isGroupSelected($group, $methodCode)
    {
        if (isset($this->visibility[$methodCode]) && in_array($group['value'], $this->visibility[$methodCode])) {
            return true;
        }
        return false;
    }

    /**
     * @return array
     */
    protected function _getPaymentMethods()
    {
        $payments = $this->dataHelper->getActivePaymentMethods();
        $methods = [];
        foreach ($payments as $paymentCode => $paymentModel) {
            $paymentTitle = $this->_scopeConfig->getValue('payment/' . $paymentCode . '/title', ScopeInterface::SCOPE_STORE);
            $methods[$paymentCode] = [
                'title' => $paymentTitle,
                'value' => $paymentCode
            ];
        }
        return $methods;
    }

    /**
     * @return array
     */
    protected function _getShippingMethods()
    {
        $shipping = $this->dataHelper->getActiveShippingMethods();
        $methods = [];

        foreach ($shipping as $shippingCode => $shippingModel) {
            $paymentTitle = $this->_scopeConfig->getValue('carriers/' . $shippingCode . '/title', ScopeInterface::SCOPE_STORE);
            $methods[$shippingCode] = [
                'title' => $paymentTitle,
                'value' => $shippingCode
            ];
        }
        return $methods;
    }
}
