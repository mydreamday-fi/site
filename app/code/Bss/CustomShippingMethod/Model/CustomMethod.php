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
 * @copyright  Copyright (c) 2017-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomShippingMethod\Model;

use Bss\CustomShippingMethod\Api\Data\CustomMethodInterface;

/**
 * Class CustomMethod
 */
class CustomMethod extends \Magento\Framework\Model\AbstractModel implements CustomMethodInterface
{

    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Initialize resource model.
     */
    protected $_eventPrefix = "bss_custom_shipping_method";

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(\Bss\CustomShippingMethod\Model\ResourceModel\CustomMethod::class);
    }

    /**
     * Get EntityId.
     *
     * @return int
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * Set EntityId
     *
     * @param int $entityId
     * @return CustomMethod|\Magento\Framework\Model\AbstractModel
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * Get Enable.
     *
     * @return bool
     */
    public function getEnable()
    {
        return $this->getData(self::ENABLE);
    }

    /**
     * Set Enable
     *
     * @param  int $enable
     * @return CustomMethod|\Magento\Framework\Model\AbstractModel
     */
    public function setEnable($enable)
    {
        return $this->setData(self::ENABLE, $enable);
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * @inheritDoc
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritDoc
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * @inheritDoc
     */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setPrice($price)
    {
        return $this->setData(self::PRICE, $price);
    }

    /**
     * @inheritDoc
     */
    public function getPrice()
    {
        return $this->getData(self::PRICE);
    }

    /**
     * @inheritDoc
     */
    public function setCalculateHandlingFee($calculateHandlingFee)
    {
        return $this->setData(self::CALCULATE_HANDLING_FEE, $calculateHandlingFee);
    }

    /**
     * @inheritDoc
     */
    public function getCalculateHandlingFee()
    {
        return $this->getData(self::CALCULATE_HANDLING_FEE);
    }

    /**
     * @inheritDoc
     */
    public function setHandlingFee($handlingFee)
    {
        return $this->setData(self::HANDLING_FEE, $handlingFee);
    }

    /**
     * @inheritDoc
     */
    public function getHandlingFee()
    {
        return $this->getData(self::HANDLING_FEE);
    }

    /**
     * @inheritDoc
     */
    public function setApplicableCountries($applicableCountries)
    {
        return $this->setData(self::APPLICABLE_COUNTRIES, $applicableCountries);
    }

    /**
     * @inheritDoc
     */
    public function getApplicableCountries()
    {
        return $this->getData(self::APPLICABLE_COUNTRIES);
    }

    /**
     * @inheritDoc
     */
    public function setSpecificCountries($specificCountries)
    {
        return $this->setData(self::SPECIFIC_COUNTRIES, $specificCountries);
    }

    /**
     * @inheritDoc
     */
    public function getSpecificCountries()
    {
        return $this->getData(self::SPECIFIC_COUNTRIES);
    }

    /**
     * @inheritDoc
     */
    public function setMinimumOrderAmount($minimumOrderAmount)
    {
        return $this->setData(self::MINIMUM_ORDER_AMOUNT, $minimumOrderAmount);
    }

    /**
     * @inheritDoc
     */
    public function getMinimumOrderAmount()
    {
        return $this->getData(self::MINIMUM_ORDER_AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function setMaximumOrderAmount($maximumOrderAmount)
    {
        return $this->setData(self::MAXIMUM_ORDER_AMOUNT, $maximumOrderAmount);
    }

    /**
     * @inheritDoc
     */
    public function getMaximumOrderAmount()
    {
        return $this->getData(self::MAXIMUM_ORDER_AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::SORT_ORDER, $sortOrder);
    }

    /**
     * @inheritDoc
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }

    /**
     * @inheritDoc
     */
    public function setSpecificRegions($specificRegions)
    {
        return $this->setData(self::SPECIFIC_REGIONS, $specificRegions);
    }

    /**
     * @inheritDoc
     */
    public function getSpecificRegions()
    {
        return $this->getData(self::SPECIFIC_REGIONS);
    }

    /**
     * @inheritDoc
     */
    public function setSpecificCountry($specificCountry)
    {
        return $this->setData(self::SPECIFIC_COUNTRY, $specificCountry);
    }

    /**
     * @inheritDoc
     */
    public function getSpecificCountry()
    {
        return $this->getData(self::SPECIFIC_COUNTRY);
    }
}
