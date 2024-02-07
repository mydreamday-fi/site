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
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Bss\CustomShippingMethod\Model\ResourceModel\CustomMethod\CollectionFactory;

/**
 * Class MassStatus
 *
 * @package Bss\CustomShippingMethod\Controller\Adminhtml\Grid
 */
class MassStatus extends \Magento\Backend\App\Action
{
    /**
     * Massactions filter.
     *
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * Mass Status.
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface.
     * @throws \Magento\Framework\Exception\LocalizedException.
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $status = (int) $this->getRequest()->getParam('status');
        foreach ($collection->getItems() as $record) {
            $record->setId($record->getEntityId());
            $record->setEnabled($status);
            $this->saveRecord($record);
        }
        $this->messageManager->addSuccessMessage(
            __('A total of %1 record(s) have been modified.', $collection->getSize())
        );
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('customshippingmethod/grid/index');
    }

    /**
     * Save record.
     *
     * @param int| $record
     * @return mixed
     */
    private function saveRecord($record)
    {
        return $record->save();
    }

    /**
     * Is Allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_CustomShippingMethod::edit')
            && $this->_authorization->isAllowed('Bss_CustomShippingMethod::view');
    }
}
