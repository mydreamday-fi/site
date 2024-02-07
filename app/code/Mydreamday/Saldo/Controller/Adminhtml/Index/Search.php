<?php

namespace Mydreamday\Saldo\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;

class Search extends Action
{
	protected $productFactory;
    protected $stockRegistry;
	protected $logger;
	protected $imageHelper;
	protected $getProductSalableQty;

    public function __construct(
        Action\Context $context,
        ProductFactory $productFactory,
        StockRegistryInterface $stockRegistry,
		LoggerInterface $logger,
		ImageHelper $imageHelper,
		GetProductSalableQtyInterface $getProductSalableQty
    ) {
        parent::__construct($context);
        $this->productFactory = $productFactory;
        $this->stockRegistry = $stockRegistry;
		$this->logger = $logger;
		$this->imageHelper = $imageHelper;
		$this->getProductSalableQty = $getProductSalableQty;
    }

    public function execute()
    {
        $response = [
            'success' => false,
            'message' => '',
            'data'    => []
        ];

        try {
            $sku = $this->getRequest()->getParam('sku');
			$stockId = 1; // Default stock id for the default stock

            // Load the product by SKU
            $product = $this->productFactory->create()->loadByAttribute('sku', $sku);

            // If product is found, get product information
            if ($product) {
                $productId = $product->getId();
                $productName = $product->getName();
                $stockItem = $this->stockRegistry->getStockItemBySku($sku);
                $qty = $stockItem->getQty();
                $salableQty = $this->getProductSalableQty->execute($sku, $stockId);
				$thumbnailUrl = $this->imageHelper->init($product, 'product_thumbnail_image')->getUrl();
				$shelfLocation = $product->getCustomAttribute('shelf_location') ? $product->getCustomAttribute('shelf_location')->getValue() : 'N/A';
                
                $response['success'] = true;
                $response['data'] = [
                    'sku' => $sku, // Including the SKU
                    'productId' => $productId,
                    'productName' => $productName,
                    'qty' => $qty,
                    'salableQty' => $salableQty, // Including the salable quantity
					'thumbnailUrl' => $thumbnailUrl, // Including the thumbnail image URL
					'shelfLocation' => $shelfLocation // Including the shelf location attribute value
                ];
            } else {
                $response['message'] = "Product with SKU {$sku} not found.";
            }

        } catch (\Exception $e) {
            $this->logger->err($e->getMessage());
            $response['message'] = $e->getMessage();
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($response);

        return $resultJson;
    }
}
