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
namespace Bss\CustomShippingMethod\Model;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;

/**
 * Class Carrier shipping method
 *
 */
class Carrier extends AbstractCarrier implements CarrierInterface
{
    const SPECIFIC_COUNTRY = 1;
    const ALL = 0;
    /**
     * @var string
     */
    protected $_code = "customshippingmethod";

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */

    protected $rateMethodFactory;

    /**
     * @var \Bss\CustomShippingMethod\Model\Rate\ResultFactory
     */
    protected $rateResultFactory;

    /**
     * @var \Bss\CustomShippingMethod\Helper\Data
     */
    protected $helper;
    /**
     * @var \Magento\Directory\Model\Region
     */
    private $region;

    /**
     * Carrier constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Bss\CustomShippingMethod\Model\Rate\ResultFactory $rateResultFactory
     * @param \Bss\CustomShippingMethod\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Bss\CustomShippingMethod\Model\Rate\ResultFactory $rateResultFactory,
        \Bss\CustomShippingMethod\Helper\Data $helper,
        \Magento\Directory\Model\Region $region,
        array $data = []
    ) {
        $this->rateMethodFactory = $rateMethodFactory;
        $this->rateResultFactory = $rateResultFactory;
        $this->helper = $helper;
        $this->region = $region;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * Collect Rate.
     *
     * @param RateRequest $request
     * @return bool|\Magento\Framework\DataObject|Result
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function collectRates(RateRequest $request)
    {
        $result = $this->rateResultFactory->create();
        if (!$this->getConfigFlag('active')) {
            return false;
        }
        $inclTax = $request->getBaseSubtotalInclTax();

        foreach ($this->checkEnabled($request) as $customMethod) {
            $minOrderAmount = $this->checkOrderAmount($customMethod['minimum_order_amount']);
            $maxOrderAmount = $this->checkOrderAmount($customMethod['maximum_order_amount']);
            if ($inclTax >= $minOrderAmount && $inclTax <= $maxOrderAmount) {
                $result->append($this->createResultMethod($request, $customMethod));
            }
        }
        return $result;
    }

    /**
     * Check Enabled
     *
     * @param \Magento\Framework\DataObject $request
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function checkEnabled(\Magento\Framework\DataObject $request)
    {
        $arrayMethod = [];

        $collectionMethod = $this->getCollectionMethod();

        $storeIdCurrent =$this->helper->getStoreManager()->getAddress()->getQuote()->getStoreId();

        foreach ($collectionMethod as $customMethod) {
            $storeIds = $this->helper->getStoreView()->selectDB($customMethod['id']);
            if (($customMethod['enabled'] == 1 && $this->isAdmin()) ||
                ($customMethod['enabled'] == 2 && !$this->isAdmin()) ||
                ($customMethod['enabled'] == 3)) {
                /*Custom Check Available Ship Countries. */
                if (!$this->checkMethodAvailable($customMethod, $request) || $customMethod['enabled']== 0) {
                    continue;
                }
                /* Check store view */
                if (in_array('0', $storeIds) || in_array($storeIdCurrent, $storeIds)) {
                    $arrayMethod [] = $customMethod;
                }
            }
        }
        $arrayMethod = $this->sortBySortOrder($arrayMethod);
        return $arrayMethod;
    }

    /**
     * Create Method
     *
     * @param RateRequest $request
     * @param array $customMethod
     * @return \Magento\Quote\Model\Quote\Address\RateResult\Method
     */
    private function createResultMethod($request, $customMethod)
    {
        $freeBoxes = $this->getFreeBoxesCount($request);
        $this->setFreeBoxes($freeBoxes);
        $shippingPrice = $this->getShippingPrice($request, $freeBoxes, $customMethod);

        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
        $method = $this->rateMethodFactory->create();

        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod($customMethod['id']);
        $method->setMethodTitle($customMethod['name']);

        $method->setPrice($shippingPrice);
        $method->setCost($shippingPrice);
        return $method;
    }

    /**
     * Check Order.
     *
     * @param array $orderAmount
     * @return float|bool
     */
    private function checkOrderAmount($orderAmount)
    {
        if ($orderAmount == null) {
            return true;
        } else {
            return (float)$orderAmount;
        }
    }

    /**
     * Allow Methods.
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        $array = [];
        foreach ($this->getCollectionMethod() as $customMethod) {
            $array[$customMethod['id']] = $customMethod['name'];
        }
        return $array;
    }

    /**
     * Get Shipping Price
     *
     * @param RateRequest $request
     * @param int $freeBoxes
     * @param array $customMethod
     * @return float
     */
    private function getShippingPrice(RateRequest $request, $freeBoxes, $customMethod)
    {
        $shippingPrice = false;
        $configPrice =  $customMethod['price'];
        if ($customMethod['type'] === 'O') {
            // per order
            $shippingPrice = $this->helper->itemPriceCalculator()
                ->getShippingPricePerOrder($request, $configPrice, $freeBoxes);
        } elseif ($customMethod['type'] === 'I') {
            // per item
            $shippingPrice = $this->helper->itemPriceCalculator()
                ->getShippingPricePerItem($request, $configPrice, $freeBoxes);
        }
        $handlingFee = (float)$customMethod['handling_fee'];
        $handlingType = $customMethod['calculate_handling_fee'];
        if (!$handlingType) {
            $handlingType = self::HANDLING_TYPE_FIXED;
        }

        $handlingAction = $this->getConfigData('handling_action');
        if (!$handlingAction) {
            $handlingAction = self::HANDLING_ACTION_PERORDER;
        }

        $shippingPrice = $handlingAction == self::HANDLING_ACTION_PERPACKAGE ? $this->_getPerpackagePrice(
            $shippingPrice,
            $handlingType,
            $handlingFee
        ) : $this->_getPerorderPrice(
            $shippingPrice,
            $handlingType,
            $handlingFee
        );
        if ($shippingPrice !== false && $request->getPackageQty() == $freeBoxes) {
            $shippingPrice = '0.00';
        }
        return $shippingPrice;
    }

    /**
     * Custom Check Available Ship Countries.
     *
     * @param array $method
     * @param \Magento\Framework\DataObject $request
     * @return $this|bool
     */
    protected function checkMethodAvailable($method, $request)
    {
        $speCountriesAllow = (int)$method['applicable_countries'];

        if ($speCountriesAllow == self::ALL) {
            return $this;
        }
        if ($speCountriesAllow == self::SPECIFIC_COUNTRY) {
            $availableCountries = explode(',', $method['specific_countries']);
            if (in_array($request->getDestCountryId(), $availableCountries)) {
                return $this;
            } else {
                return false;
            }
        } else {
            $availableCountry = $method['specific_country'] ?? '';
            if (($request->getDestCountryId() == $availableCountry)) {
                $availableRegions = explode(',', $method['specific_regions']) ?? [''];
                $regionId = $request->getDestRegionId();
                $regionName = $regionId ? $this->region->load($regionId)->getName() : $request->getDestRegionCode();
                if ($availableRegions && in_array($this->validateText($regionName), $this->validateText($availableRegions))) {
                    return $this;
                } else {
                    return false;
                }
            }
        }
        return false;
    }

    /**
     * Modify text or array to lowercase & remove blank.
     *
     * @param mixed $input
     * @return mixed
     */
    public function validateText($input){
        if (is_array($input)){
            return  array_map('strtolower', array_map('trim', $input));
        } elseif (is_string($input)){
            return strtolower(trim($input));
        }
        return false;
    }

    /**
     * Check Show Method
     *
     * @param \Magento\Framework\DataObject $request
     * @return bool|false|\Magento\Framework\Model\AbstractModel|\Magento\Quote\Model\Quote\Address\RateResult\Error|AbstractCarrier
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function checkAvailableShipCountries(\Magento\Framework\DataObject $request)
    {
        $showMethod = $this->getConfigData('showmethod');
        if (empty($this->checkEnabled($request)) && $showMethod) {
            $error = $this->_rateErrorFactory->create();

            $error->setCarrier($this->_code);
            $error->setCarrierTitle($this->getConfigData('title'));

            $errorMsg = $this->getConfigData('specificerrmsg');
            $error->setErrorMessage(
                $errorMsg ? $errorMsg : __(
                    'Sorry, but we can\'t deliver to the destination country with this shipping module.'
                )
            );

            return $error;
        }
        return parent::checkAvailableShipCountries($request);
    }

    /**
     * Is Admin.
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function isAdmin()
    {
        return $this->helper->getState()->getAreaCode() == FrontNameResolver::AREA_CODE;
    }

    /**
     * Free Boxes.
     *
     * @param mixed $item
     * @return mixed
     */
    private function getFreeBoxesCountFromChildren($item)
    {
        $freeBoxes = 0;
        foreach ($item->getChildren() as $child) {
            if ($child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
                $freeBoxes += $item->getQty() * $child->getQty();
            }
        }
        return $freeBoxes;
    }

    /**
     * Free Box count.
     *
     * @param RateRequest $request
     * @return int
     */
    private function getFreeBoxesCount(RateRequest $request)
    {
        $freeBoxes = 0;
        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }

                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    $freeBoxes += $this->getFreeBoxesCountFromChildren($item);
                } elseif ($item->getFreeShipping()) {
                    $freeBoxes += $item->getQty();
                }
            }
        }
        return $freeBoxes;
    }

    /**
     * Get CollectionMethod.
     *
     * @return array
     */
    public function getCollectionMethod()
    {
        $collection = $this->helper->getCollectionMethod()->create();
        return $collection->getData();
    }

    /**
     * Sort By Sort Order
     *
     * @param array| $array
     * @return array.
     */
    protected function sortBySortOrder($array)
    {
        uasort($array, function ($a, $b) {
            return $a['sort_order'] - $b['sort_order'];
        });
        return $array;
    }
}
