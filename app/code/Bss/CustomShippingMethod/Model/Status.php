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

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Status
 *
 * @package Bss\CustomShippingMethod\Model
 */
class Status implements OptionSourceInterface
{
    /**
     * Get Grid row status type labels array.
     *
     * @return array.
     */
    public function getOptionArray()
    {
        $options = [
            '0' => __('Disabled'),
            '1' => __('Admin'),
            '2' => __('Frontend'),
            '3' => __('Both Admin and Frontend')
        ];
        return $options;
    }

    /**
     * Get Grid row status labels array with empty value for option element.
     *
     * @return array
     */
    public function getAllOptions()
    {
        $res = $this->getOptions();
        array_unshift($res, ['value' => '', 'label' => '']);
        return $res;
    }

    /**
     * Get Grid row type array for option element.
     *
     * @return array
     */
    public function getOptions()
    {
        $res = [];
        foreach ($this->getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }
        return $res;
    }

    /**
     * Get Grid row type array for option element.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getOptions();
    }
}
