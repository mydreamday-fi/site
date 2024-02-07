<?php

namespace Meetanshi\PayshipRestriction\Controller\Adminhtml\PayshipRestriction;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;
use Meetanshi\PayshipRestriction\Model\ResourceModel\PayshipRestriction\CollectionFactory;
use Meetanshi\PayshipRestriction\Model\PayshipRestrictionFactory;

/**
 * Class Save
 */
class Save extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var array
     */
    protected $availableTypes = ['payment', 'shipping'];
    /**
     * @var
     */
    protected $messageManager;
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    /**
     * @var PayshipRestrictionFactory
     */
    protected $payshipFactory;

    /**
     * Save constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param CollectionFactory $collectionFactory
     * @param PayshipRestrictionFactory $payshipFactory
     */
    public function __construct(Context $context, PageFactory $resultPageFactory, CollectionFactory $collectionFactory, PayshipRestrictionFactory $payshipFactory)
    {
        parent::__construct($context);
        $this->collectionFactory = $collectionFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->payshipFactory = $payshipFactory;
    }

    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $type = $this->getRequest()->getParam('type');

        if (!in_array($type, $this->availableTypes)) {
            $this->messageManager->addError(__('Unable to save. Wrong type specified.'));
            $this->_redirect('*/*', ['type' => 'payment', '_current' => true]);
        }
        $websiteId = $this->getRequest()->getParam('website_id', 1);
        $methods = $this->getRequest()->getPost('methods');
        $methodCodes = $this->getRequest()->getPost('methods_codes');

        foreach ($methodCodes as $methodCode) {
            $groups = isset($methods[$methodCode]) ? $methods[$methodCode] : [];
            $visibilitys = $this->collectionFactory->create();
            $visibilitys->addFieldToFilter('type', ['eq' => $type]);
            $visibilitys->addFieldToFilter('website_id', ['eq' => $websiteId]);
            $visibilitys->addFieldToFilter('method', ['eq' => $methodCode]);

            if (count($visibilitys) > 0) {
                foreach ($visibilitys as $visibility) {
                    $id = $visibility->getRestrictionId();
                }
                if ($id) {
                    $modelUpdate = $this->payshipFactory->create();
                    $modelUpdate->load($id);
                    $modelUpdate->setCustomerGroupIds(implode(',', $groups));
                    $modelUpdate->save();
                }
            } else {
                $modelInsert = $this->payshipFactory->create();
                $modelInsert->setType($type);
                $modelInsert->setWebsiteId($websiteId);
                $modelInsert->setMethod($methodCode);
                $modelInsert->setCustomerGroupIds(implode(',', $groups));
                $modelInsert->save();
            }
        }

        $this->messageManager->addSuccess(__($type . ' options have been saved.'));
        
        $path = '*/*/' . $type."/website_id/".$websiteId;
        $resultRedirect->setPath($path);
        return $resultRedirect;
    }
}
