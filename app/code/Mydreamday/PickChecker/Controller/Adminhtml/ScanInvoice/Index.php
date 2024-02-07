<?php
namespace Mydreamday\PickChecker\Controller\Adminhtml\ScanInvoice;
use Magento\Backend\App\Action;
class Index extends Action
{
	protected $_coreRegistry = null;
	protected $resultPageFactory;

    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        parent::__construct($context);
    }
	
    protected function _isAllowed()
    {
        return true;
    }

    protected function _initAction()
    {
        $resultPage = $this->resultPageFactory->create();
        return $resultPage;
    }

   
    public function execute()
    {
        
/** $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/LOGxx.log');
$logger = new \Zend\Log\Logger();
$logger->addWriter($writer);
$logger->info('TEXT'); */

        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()->prepend(__('Pick Checker'));
        $resultPage->getConfig()->getTitle()->prepend(__('Pick Checker'));
        return $resultPage;
    }
}
