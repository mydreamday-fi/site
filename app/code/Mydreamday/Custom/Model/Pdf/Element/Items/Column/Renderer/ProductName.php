<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */

namespace Mydreamday\Custom\Model\Pdf\Element\Items\Column\Renderer;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Bundle item pdf renderer
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class ProductName extends \Magetrend\PdfTemplates\Model\Pdf\Element\Items\Column\Renderer\ProductName
{
    public function getPdfData()
    {
		/* $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);
		$logger->info('custom overriderrr'); */
		
        $attributes = $this->getAttributes();
        $columnName = $this->getColumn();
        $item = $this->getItem();

        $itemName = $this->getItemName();
		$shelfLocation = $this->getShelfLocation();

        $showImage = false;
        if (isset($attributes['show_image']) && $attributes['show_image'] != 'false') {
            $showImage = true;
        }

        $fontSize = $this->moduleHelper->removePx($attributes['table_row_product_line_1_size']);
        $lineHeight = $this->moduleHelper->removePx($attributes['table_row_product_line_1_line_height']);
        $fontCode = $attributes['table_row_product_line_1_font'];
        $color = $attributes['table_row_product_line_1_color'];

        $fontSize2 = $this->moduleHelper->removePx($attributes['table_row_product_line_2_size']);
        $fontCode2 = $attributes['table_row_product_line_2_font'];
        $color2 = $attributes['table_row_product_line_2_color'];
        $lineHeight2 = $this->moduleHelper->removePx($attributes['table_row_product_line_2_line_height']);

        $padding = $this->getRowPadding();

        $columnWidth = $this->moduleHelper->removePx($attributes['table_header_'.$columnName.'_column_width'])
            - $padding[3] - $padding[1];
        if ($showImage) {
            $columnWidth  = $columnWidth - $attributes['image_width'] -  $attributes['image_margin_left']
                - $attributes['image_margin_right'];
        }
        $columnWidth = $this->moduleHelper->toPoint($columnWidth);

        $options = $this->getItemOptions();
        $productOptions = $this->element->splitStringToLines(
            $options,
            $columnWidth,
            $fontCode2,
            $this->moduleHelper->toPoint($fontSize2)
        );
		
		$product = $this->productRepository->getById($item->getProductId());
        $shelfLocation = $product->getData('shelf_location');
		if($product->getTypeId() == 'configurable'){
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
			$connection = $resource->getConnection();
			
			$sql = "Select * FROM sales_order_item Where item_id = ". $item->getOrderItemId();
			$result = $connection->fetchAll($sql);
			$childData = @current($result);
			
			$simple_sku = $childData['sku'];
			$childShelfLocation = '';
			if($simple_sku){
				$_product = $objectManager->get('\Magento\Catalog\Api\ProductRepositoryInterface')->get($simple_sku);
				$childShelfLocation = $_product->getData('shelf_location');
				
				//$logger->info('child shelfLocation : ' . $shelfLocation);
			} 
			$shelfLocation = $childShelfLocation ? $childShelfLocation : $shelfLocation;
		}
		//$productSKU = $product->getData('sku');
		//$shelfAndSKU = $productSKU . ', ' . $shelfLocation;
		
		$productLocation = $this->element->splitStringToLines(
            $shelfLocation,
            $columnWidth,
            $fontCode2,
            $this->moduleHelper->toPoint($fontSize2)
        );

        $productName = $this->element->splitStringToLines(
            $itemName,
            $columnWidth,
            $fontCode,
            $this->moduleHelper->toPoint($fontSize)
        );

        $rowHeight = $padding[0] + count($productName) * $lineHeight
            + count($productOptions) * $lineHeight2 + $padding[2];

        $data =  [
            'height' => $rowHeight,
            'padding' => $padding,
            'text' => [
                'product_name' => [
                    'text' => $productName,
                    'font' => $fontCode,
                    'font_size' => $fontSize,
                    'line_height' => $lineHeight,
                    'color' => $color
                ],
                'product_option' => [
                    'text' => $productOptions,
                    'font' => $fontCode2,
                    'font_size' => $fontSize2,
                    'line_height' => $lineHeight2,
                    'color' => $color2,
                    'separators' => $this->getProductOptionLabels()
                ],
                'product_shelf_location' => [
                    'text' => $productLocation,
                    'font' => $fontCode2,
                    'font_size' => $fontSize2,
                    'line_height' => $lineHeight2,
                    'color' => $color2
                ]
            ]
        ];

        if ($showImage) {
            $data['image'] = [
                'path' => $this->getItemImage(),
                'width' => $attributes['image_width'],
                'top' => $attributes['image_margin_top'],
                'right' => $attributes['image_margin_right'],
                'bottom' => $attributes['image_margin_bottom'],
                'left' => $attributes['image_margin_left'],
            ];
        }

        return $data;
    }
	
	public function getProductOptionLabels()
    {
        if (!$this->moduleHelper->splitProductOption($this->getItem()->getStoreId())) {
            return [];
        }

        $options = $this->getItemRenderer()->getItemOptions();
        if (!$options) {
            return [];
        }

        $labels = [];
        foreach ($options as $option) {
            $labels[] = $option['label']. ': ';
        }
        return $labels;
    }

    public function getItemName()
    {
        $item = $this->getItem();
        $itemName = $item->getName();
        if ($this->moduleHelper->singleStoreMode()) {
            try {
                $product = $this->productRepository->getById($item->getProductId());
                $itemName = $product->getName();
            } catch (NoSuchEntityException $e) {}
        }

        return $itemName;
    }
}
