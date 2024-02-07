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

use Bss\CustomShippingMethod\Api\ManagementInterface;
use Bss\CustomShippingMethod\Helper\Data as HelperData;

/**
 * Class Management
 *
 * @package Bss\CustomShippingMethod\Model
 */
class Management implements ManagementInterface
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * Management constructor.
     * @param HelperData $helperData
     */
    public function __construct(
        HelperData $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * @inheritDoc
     */
    public function getConfig($storeId = null)
    {
        $websiteId = $this->helperData->getWebsiteId($storeId);
        return [
            "configs" => [
                "enable" => (bool) $this->helperData->getConfigModuleWebsite(HelperData::CONFIG_ENABLE, $websiteId),
                "title" => $this->helperData->getConfigModuleStoreView(HelperData::CONFIG_TITLE, $storeId),
                "show_method"  => (bool) $this->helperData->getConfigModuleWebsite(HelperData::CONFIG_SHOW_METHOD, $websiteId),
                "display_error_message" => $this->helperData->getConfigModuleStoreView(HelperData::CONFIG_SPECIFIERRMSG, $storeId),
                "sort_order" => (int) $this->helperData->getConfigModuleWebsite(HelperData::CONFIG_SORT_ORDER, $websiteId)
            ]
        ];
    }
}
