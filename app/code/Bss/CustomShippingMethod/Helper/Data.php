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
 * @category  BSS
 * @package   Bss_AdminShippingMethod
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomShippingMethod\Helper;

use Bss\CustomShippingMethod\Model\ResourceModel\StoreView;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\State;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 *
 * @package Bss\CustomShippingMethod\Helper
 */
class Data extends AbstractHelper
{
    const CONFIG_TITLE = "carriers/customshippingmethod/title";
    const CONFIG_SHOW_METHOD = "carriers/customshippingmethod/showmethod";
    const CONFIG_SPECIFIERRMSG = "carriers/customshippingmethod/specificerrmsg";
    const CONFIG_SORT_ORDER = "carriers/customshippingmethod/sort_order";
    const CONFIG_ENABLE = "carriers/customshippingmethod/active";

    /**
     * @var State
     */
    protected $state;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var StoreView
     */
    protected $storeView;

    /**
     * @var \Bss\CustomShippingMethod\Model\ResourceModel\CustomMethod\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\OfflineShipping\Model\Carrier\Flatrate\ItemPriceCalculator
     */
    private $itemPriceCalculator;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param \Magento\Sales\Block\Adminhtml\Order\Create\Shipping\Method\Form $form
     * @param \Magento\OfflineShipping\Model\Carrier\Flatrate\ItemPriceCalculator $itemPriceCalculator
     * @param \Bss\CustomShippingMethod\Model\ResourceModel\CustomMethod\CollectionFactory $collectionFactory
     * @param State $state
     * @param StoreView $storeView
     */
    public function __construct(
        Context $context,
        \Magento\Sales\Block\Adminhtml\Order\Create\Shipping\Method\Form $form,
        \Magento\OfflineShipping\Model\Carrier\Flatrate\ItemPriceCalculator $itemPriceCalculator,
        \Bss\CustomShippingMethod\Model\ResourceModel\CustomMethod\CollectionFactory $collectionFactory,
        State $state,
        StoreView $storeView
    ) {
        parent::__construct($context);
        $this->state = $state;
        $this->storeManager = $form;
        $this->storeView = $storeView;
        $this->collectionFactory = $collectionFactory;
        $this->itemPriceCalculator = $itemPriceCalculator;
    }

    /**
     * Get Store ID Admin
     *
     * @return \Magento\Sales\Block\Adminhtml\Order\Create\Shipping\Method\Form|\Magento\Store\Model\StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->storeManager;
    }

    /**
     * Get State.
     *
     * @return State
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Get Store View.
     *
     * @return StoreView
     */
    public function getStoreView()
    {
        return $this->storeView;
    }

    /**
     * Get Collection Method.
     *
     * @return \Bss\CustomShippingMethod\Model\ResourceModel\CustomMethod\CollectionFactory
     */
    public function getCollectionMethod()
    {
        return $this->collectionFactory;
    }

    /**
     * ItemPriceCalculator
     *
     * @return \Magento\OfflineShipping\Model\Carrier\Flatrate\ItemPriceCalculator
     */
    public function itemPriceCalculator()
    {
        return $this->itemPriceCalculator;
    }

    /**
     * Get config module scope website
     *
     * @param int|null $webisteId
     * @param string $path
     * @return mixed
     */
    public function getConfigModuleWebsite($path, $webisteId = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_WEBSITE,
            $webisteId
        );
    }

    /**
     * Get config module scope store view
     *
     * @param int|null $storeId
     * @param string $path
     * @return mixed
     */
    public function getConfigModuleStoreView($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get website ID by store view id
     *
     * @param int $storeViewId
     * @return int|null
     */
    public function getWebsiteId($storeViewId)
    {
        try {
            return $this->storeManager->getStore($storeViewId)->getWebsiteId();
        } catch (\Exception $exception) {
            $this->_logger->critical($exception->getMessage());
            return null;
        }

    }

    public function getTypeCustomShipping($typeCode)
    {
        $allType = [
        ["value" =>'',"label" => __("None")],
        ["value" => "O","label" => __("Per Order")],
        ["value"  => "I","label" => __("Per Item")]
        ];
        foreach ($allType as $type) {
            if ($type["value"] == $typeCode) {
                return $type["label"];
            }
        }
        return __("None");
    }
}
