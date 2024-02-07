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
namespace Bss\CustomShippingMethod\Model\ResourceModel;

use Bss\CustomShippingMethod\Api\CustomMethodRepositoryInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Bss\CustomShippingMethod\Api\Data\CustomMethodStoreInterface;
use Bss\CustomShippingMethod\Api\Data\CustomMethodInterface;

/**
 * Bss\CustomMethod\Model\ResourceModel
 */
class CustomMethodRepository implements CustomMethodRepositoryInterface
{
    /**
     * @var \Bss\CustomShippingMethod\Model\ResourceModel\StoreView
     */
    protected $storeView;

    /**
     * @var \Bss\CustomShippingMethod\Model\CustomMethodFactory
     */
    protected $customMethod;

    /**
     * @var CustomMethod
     */
    protected $customMethodResource;

    /**
     * @var \Bss\CustomShippingMethod\Model\Status
     */
    protected $status;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $criteriaBuilder;

    /**
     * @var CollectionProcessor
     */
    protected $collectionProcessor;

    /**
     * @var CustomMethod\CollectionFactory
     */
    protected $customMethodCollection;

    /**
     * @var SearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * CustomMethodRepository constructor.
     *
     * @param \Bss\CustomShippingMethod\Model\CustomMethodFactory $customMethod
     * @param CustomMethod $customMethodResource
     * @param \Bss\CustomShippingMethod\Model\Status $status
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder
     * @param CollectionProcessor $collectionProcessor
     * @param CustomMethod\CollectionFactory $customMethodCollection
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        StoreView $storeView,
        \Bss\CustomShippingMethod\Model\CustomMethodFactory $customMethod,
        \Bss\CustomShippingMethod\Model\ResourceModel\CustomMethod $customMethodResource,
        \Bss\CustomShippingMethod\Model\Status $status,
        \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder,
        CollectionProcessor $collectionProcessor,
        \Bss\CustomShippingMethod\Model\ResourceModel\CustomMethod\CollectionFactory $customMethodCollection,
        SearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->storeView = $storeView;
        $this->customMethod = $customMethod;
        $this->customMethodResource = $customMethodResource;
        $this->status = $status;
        $this->criteriaBuilder = $criteriaBuilder;
        $this->collectionProcessor = $collectionProcessor;
        $this->customMethodCollection = $customMethodCollection;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $collection = $this->customMethodCollection->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritdoc
     */
    public function getListCustomShipping()
    {
        $labelStatus = $this->status->getOptions();
        $data = [];
        $list = $this->getList($this->criteriaBuilder->create());
        foreach ($list->getItems() as $key => $item) {
            $data[$key]["id"] = $item->getId();
            if (isset($labelStatus[$item->getEnable()])) {
                $data[$key]["enable"]["value"] = $labelStatus[$item->getEnable()];
            }
            $data[$key]["name"] = $item->getName();
            $data[$key]["sort_order"] = $item->getSortOrder();
        }
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function getById($id)
    {
        $customMethod = $this->customMethod->create();
        $this->customMethodResource->load($customMethod, $id);
        if ($customMethod->getId()) {
            $storeIds = implode(",", $customMethod->getStoreId());
            $customMethod->setStoreId($storeIds);
            return $customMethod;
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getListCustomShippingStore($storeId)
    {
        $methodIds = $this->storeView->getMethodIds($storeId);
        $searchCriteriaBuilder = $this->criteriaBuilder->addFilter('id', $methodIds, "in");
        return $this->getList($searchCriteriaBuilder->create())->getItems();
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function delete($id)
    {
        $customMethod = $this->getById($id);
        if ($customMethod) {
            try {
                $this->customMethodResource->delete($customMethod);
                $result["status"] = [
                    "success" => true,
                    "message" => __("You deleted.")
                ];
            } catch (\Exception $exception) {
                throw new \Exception(__('%1', $exception->getMessage()));
            }
        } else {
            $result["status"] = [
                "success" => false,
                "message" => __("No such entity with id: %1", $id)
            ];
        }
        return $result;
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function save($customMethod)
    {
        $this->validateInput($customMethod);
        try {
            $this->customMethodResource->save($customMethod);
            return $customMethod;
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save with name: %1', $customMethod->getName())
            );
        }
    }

    /**
     * Validate input
     *
     * @param CustomMethodStoreInterface|CustomMethodInterface $customMethod
     * @throws InputException
     * @throws \Exception
     */
    public function validateInput($customMethod)
    {
        if (!$customMethod->getName()) {
            throw new InputException(__('Input name is required. Enter and try again.'));
        }
        if (!$customMethod->getStoreId()) {
            throw new InputException(__('Input store_id is required. Enter and try again.'));
        }
        $this->checkAmount($customMethod);
    }

    /**
     * Check Amount
     *
     * @param CustomMethodStoreInterface|CustomMethodInterface $customMethod
     * @throws \Exception
     */
    protected function checkAmount($customMethod)
    {
        if ($customMethod->getMinimumOrderAmount() && $customMethod->getMaximumOrderAmount() &&
            $customMethod->getMinimumOrderAmount() >= $customMethod->getMaximumOrderAmount()
        ) {
            throw new \Exception(__("Min Order Amount < Max Order Amount"));
        }
    }
}
