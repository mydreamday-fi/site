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
namespace Bss\CustomShippingMethod\Controller\Adminhtml\Grid;

use Magento\Framework\Controller\ResultFactory;

/**
 * Class AddCustomMethod
 *
 * @package Bss\CustomShippingMethod\Controller\Adminhtml\Grid
 */
class AddCustomMethod extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Bss\CustomShippingMethod\Model\CustomMethodFactory
     */
    private $customMethodFactory;

    /**
     * AddCustomMethod constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Bss\CustomShippingMethod\Model\CustomMethodFactory $customMethodFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Bss\CustomShippingMethod\Model\CustomMethodFactory $customMethodFactory
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->customMethodFactory = $customMethodFactory;
    }

    /**
     * Mapped Grid List page.
     *
     * @return void|\Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $rowId = (int) $this->getRequest()->getParam('id');
        $rowData = $this->customMethodFactory->create();

        if ($rowId) {
            $rowData = $rowData->load($rowId);
            if (!$rowData->getEntityId()) {
                $this->messageManager->addErrorMessage(__('row data no longer exist.'));
                $this->_redirect('*/*/index');
                return;
            }
        }

        $this->coreRegistry->register('row_data', $rowData);
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $title = __('Custom Shipping Method Configuration');
        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;
    }

    /**
     * Allow custom_shipping_method.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_CustomShippingMethod::edit')
            && $this->_authorization->isAllowed('Bss_CustomShippingMethod::view');
    }
}
