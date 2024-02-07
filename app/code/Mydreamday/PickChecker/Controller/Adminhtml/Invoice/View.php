<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Mydreamday\PickChecker\Controller\Adminhtml\Invoice;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;

class View extends \Magento\Sales\Controller\Adminhtml\Invoice\AbstractInvoice\View implements HttpGetActionInterface
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context, $registry, $resultForwardFactory);
    }

    /**
     * Invoice information page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $invoice = $this->getInvoice();
        if(!$invoice){
            /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
            $resultForward = $this->resultForwardFactory->create();
            return $resultForward->forward('noroute');
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_Sales::sales_order');
        $resultPage->getConfig()->getTitle()->prepend(__('Pick Checker'));
        $resultPage->getConfig()->getTitle()->prepend(sprintf("Pick checking invoice #%s", $invoice->getIncrementId()));
        $resultPage->getLayout()->getBlock(
            'sales_invoice_view'
        )->updateBackButtonUrl(
            $this->getRequest()->getParam('come_from')
        );
        return $resultPage;
    }
    
    protected function getInvoice()
    {
        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $invoice = $objectManager->get('\Magento\Sales\Model\Order\InvoiceFactory')->create()->loadByIncrementId($this->getRequest()->getParam('invoice_id'));
            $this->registry->register('current_invoice', $invoice);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Invoice capturing error'));
            return false;
        }

        return $invoice;
    }
}
