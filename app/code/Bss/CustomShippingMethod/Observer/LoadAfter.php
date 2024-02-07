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
 * @package   Bss_CustomShippingMethod.
 * @author    Extension Team
 * @copyright Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomShippingMethod\Observer;

use Magento\Framework\Event\ObserverInterface;
use Bss\CustomShippingMethod\Model\ResourceModel\StoreView;

/**
 * Class SaveAfter
 *
 * @package Bss\CustomShippingMethod\Observer
 */
class LoadAfter implements ObserverInterface
{
    /**
     * @var StoreView
     */
    protected $storeView;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * SaveAfter constructor.
     *
     * @param StoreView $storeView
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        StoreView $storeView,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->storeView = $storeView;
        $this->messageManager = $messageManager;
    }

    /**
     * Set storeId after Load Data
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $data = $observer->getEvent()->getDataObject();
        $id = $data->getId();
        if (isset($id)) {
            $storeIds = $this->storeView->selectDB($id);
            $data->setStoreId($storeIds);
        }
        return $this;
    }
}
