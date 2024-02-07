<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * @category MageTrend
 * @package  Magetend/PdfCart2Quote
 * @author   Edvstu <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */

namespace Magetrend\PdfCustomSort\Plugin;

use Magento\Framework\Exception\NoSuchEntityException;

class Items
{
    public $productRepository;

    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
       $this->productRepository = $productRepository;
    }

    public function afterGetAllItems($subject, $items)
    {
        if (!$subject->getSource() instanceof \Magento\Sales\Model\Order\Invoice) {
           return $items;
        }

        $itemMap = [];
        foreach ($items as $i => $item) {
            $product = false;
            try {
                $product = $this->productRepository->get($item->getSku());
                $sortAttr = $product->getShelfLocation();
            } catch ( NoSuchEntityException $e) {
            }

            if (!$product){
                try {
                    $product = $this->productRepository->getById($item->getProductId());
                    $sortAttr = $product->getShelfLocation();
                } catch ( NoSuchEntityException $e) {
                }
            }

            if (empty($sortAttr)) {
                $sortAttr = 'ZZZZZ-99999-9999'.$i;
            }

            $itemMap[$item->getId()] = $sortAttr;
        }

        uasort($itemMap, [$this, 'sortItemMap']);

        $mapItems = [];
        foreach ($items as $item) {
            $mapItems[$item->getId()] = $item;
        }

        $sortedItems = [];
        foreach ($itemMap as $itemId => $sortOrder) {
            $item = $mapItems[$itemId];
            $sortedItems[] = $item;
        }

        return $sortedItems;
    }
	
	public function sortItemMap($a, $b)
	{
		$a = explode('-', $a);
		$b = explode('-', $b);

		$p1 = strcmp($a[0], $b[0]);
		if ($p1 != 0) {
			return $p1; // strcmp redan returnerar -1, 0 eller 1
		}

		if (!isset($b[2])) {
			$b[2] = 1;
		} 

		if (!isset($a[2])) {
			$a[2] = 1;
		}

		if ($a[2] != $b[2]) {
			return $a[2] > $b[2] ? 1 : -1;
		}

		if ($a[1] != $b[1]) {
			return $a[1] > $b[1] ? 1 : -1;
		}

		return 0; // returnera 0 om de är lika
	}

}
