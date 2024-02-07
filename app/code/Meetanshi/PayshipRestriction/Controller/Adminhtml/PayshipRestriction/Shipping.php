<?php

namespace Meetanshi\PayshipRestriction\Controller\Adminhtml\PayshipRestriction;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Shipping
 */
class Shipping extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Shipping constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(Context $context, PageFactory $resultPageFactory)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Meetanshi_PayshipRestriction::menu_shipping');
    }

    /**
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Meetanshi_PayshipRestriction::menu_payment');
        $resultPage->getConfig()->getTitle()->prepend(__('Shipping Methods Restriction by Customer Groups'));
        $resultPage->addBreadcrumb(__('Shipping Methods Restriction by Customer Groups'), __('Shipping Methods Restriction by Customer Groups'));
        return $resultPage;
    }
}
