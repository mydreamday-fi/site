<?php

namespace Mydreamday\Saldo\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;

class UpdateQty extends Action
{
    protected $resultJsonFactory;
    protected $productRepository;
    protected $stockRegistry;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ProductRepositoryInterface $productRepository,
        StockRegistryInterface $stockRegistry
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productRepository = $productRepository;
        $this->stockRegistry = $stockRegistry;
    }

    public function execute()
	{
		$resultJson = $this->resultJsonFactory->create();
		try {
			$postData = $this->getRequest()->getPostValue();
			$sku = isset($postData['sku']) ? $postData['sku'] : null;
			$newQty = isset($postData['newQuantity']) ? $postData['newQuantity'] : null;
			$newShelfLocation = isset($postData['newShelfLocation']) ? $postData['newShelfLocation'] : null;

			if ($sku) {
				$product = $this->productRepository->get($sku);
				
				if ($newQty !== null) {
					$stockItem = $this->stockRegistry->getStockItemBySku($sku);
					$stockItem->setQty($newQty);
					$stockItem->setIsInStock((bool)$newQty);
					$this->stockRegistry->updateStockItemBySku($sku, $stockItem);
				}

				if ($newShelfLocation !== null) {
					$product->setCustomAttribute('shelf_location', $newShelfLocation);
					$this->productRepository->save($product);
				} else {
					$newShelfLocation = $product->getCustomAttribute('shelf_location')->getValue();
				}

				return $resultJson->setData(['success' => true, 'data' => ['shelfLocation' => $newShelfLocation]]);
				
			} else {
				throw new \Exception('SKU is missing.');
			}

		} catch (\Exception $e) {
			return $resultJson->setData(['success' => false, 'message' => $e->getMessage()]);
		}
	}

    protected function _isAllowed()
    {
        // You may define a specific ACL rule here or allow access
        return true;
    }
}
