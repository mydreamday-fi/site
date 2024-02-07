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
namespace Bss\CustomShippingMethod\Api\Data;

/**
 * Interface CustomMethodInterface
 * @package Bss\CustomShippingMethod\Api\Data
 */
interface CustomMethodInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const ENTITY_ID = 'id';
    const ENABLE = 'enabled';
    const NAME = "name";
    const TYPE = "type";
    const PRICE = "price";
    const CALCULATE_HANDLING_FEE  = "calculate_handling_fee";
    const HANDLING_FEE = "handling_fee";
    const APPLICABLE_COUNTRIES = "applicable_countries";
    const SPECIFIC_COUNTRIES = "specific_countries";
    const MINIMUM_ORDER_AMOUNT = "minimum_order_amount";
    const MAXIMUM_ORDER_AMOUNT = "maximum_order_amount";
    const SORT_ORDER = "sort_order";
    const SPECIFIC_REGIONS = "specific_regions";
    const SPECIFIC_COUNTRY = "specific_country";

    /**
     * Get EntityId.
     *
     * @return int
     */
    public function getEntityId();

    /**
     * Set EntityId.
     *
     * @param int $entityId
     * @return int
     */
    public function setEntityId($entityId);

    /**
     * Get Enable.
     *
     * @return int
     */
    public function getEnable();

    /**
     * Set Enable.
     *
     * @param int $enable
     * @return int
     */
    public function setEnable($enable);

    /**
     * Get id
     *
     * @return int
     */
    public function getId();

    /**
     * Set name
     *
     * @param $name
     * @return $this
     */
    public function setName($name);

    /**
     * Get name
     *
     * @return string
     */
    public function getName();

    /**
     * Set type
     * @param string $type
     *
     * @return $this
     */
    public function setType($type);

    /**
     * Get type
     *
     * @return string
     */
    public function getType();

    /**
     * Set price
     *
     * @param float $price
     * @return $this
     */
    public function setPrice($price);

    /**
     * Get price
     *
     * @return float
     */
    public function getPrice();

    /**
     * St calculate hangling fee
     *
     * @param string $calculateHandlingFee
     * @return $this
     */
    public function setCalculateHandlingFee($calculateHandlingFee);

    /**
     * Get calculate handling fee
     *
     * @return string
     */
    public function getCalculateHandlingFee();

    /**
     * Set handling fee
     *
     * @param FLoat $handlingFee
     * @return mixed
     */
    public function setHandlingFee($handlingFee);

    /**
     * get Handling fee
     *
     * @return float
     */
    public function getHandlingFee();

    /**
     * Set applicable Countries
     *
     * @param int  $applicableCountries
     * @return $this
     */
    public function setApplicableCountries($applicableCountries);

    /**
     * Get applicable countries
     *
     * @param $applicableCountries
     * @return int
     */
    public function getApplicableCountries();

    /**
     * Set specific countries
     *
     * @param string $specificCountries
     * @return $this
     */
    public function setSpecificCountries($specificCountries);

    /**
     * Get specific countries
     *
     * @return string
     */
    public function getSpecificCountries();

    /**
     * Set minimum order amount
     *
     * @param float $minimumOrderAmount
     * @return mixed
     */
    public function setMinimumOrderAmount($minimumOrderAmount);

    /**
     * Get Minimum Order Amount
     *
     * @return float
     */
    public function getMinimumOrderAmount();

    /**
     * set Maximum Order Amount
     *
     * @param float $maximumOrderAmount
     * @return $this
     */
    public function setMaximumOrderAmount($maximumOrderAmount);

    /**
     * Get Maximum Order Amount
     *
     * @return float
     */
    public function getMaximumOrderAmount();

    /**
     * Set sort order
     *
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder);

    /**
     * Get sort order
     *
     * @return mixed
     */
    public function getSortOrder();

    /**
     * Set Specific Regions
     *
     * @param string $specificRegions
     * @return $this
     */
    public function setSpecificRegions($specificRegions);

    /**
     * Get Specific Regions
     *
     * @return string
     */
    public function getSpecificRegions();

    /**
     * Set Specific Country
     *
     * @param string $specificCountry
     * @return $this
     */
    public function setSpecificCountry($specificCountry);

    /**
     * Get Specific country
     *
     * @return string
     */
    public function getSpecificCountry();

}
