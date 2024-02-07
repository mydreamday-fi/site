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

class Check extends \Magento\Backend\App\Action
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
        parent::__construct($context);
    }

    /**
     * Invoice information page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $invoice_id = $this->getRequest()->getParam('invoice_id');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $invoice = $objectManager->get('\Magento\Sales\Model\Order\InvoiceFactory')->create()->loadByIncrementId($invoice_id);
        if(!$invoice->getId()){
            echo 'fail';
            exit;
        }
        echo $this->getUrl('pickchecker/invoice/view/', ['invoice_id' => $invoice_id]);
    }

    protected function _isAllowed()
    {
        return true;
    }
}
