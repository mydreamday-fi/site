<?php
namespace Mydreamday\Custom\Plugin\Model;

use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface;
use Magento\Framework\App\RequestInterface;

/**
 * Check if it is necessary to show qty left.
 */
class IsSalableQtyAvailableForDisplaying
{
    /**
     * @var StockItemConfigurationInterface
     */
    private $stockItemConfig;

    /**
     * @param \Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface $stockItemConfiguration
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        StockItemConfigurationInterface $stockItemConfiguration,
        RequestInterface $request
    ) {
        $this->stockItemConfig = $stockItemConfiguration;
        $this->request = $request;
    }

    public function afterExecute($subject, $result, float $productSalableQty): bool
    {
        $params = $this->request->getParams();
        if (!isset($params['isChild'])) {
            return $result;
        }

        return ($this->stockItemConfig->getBackorders() === StockItemConfigurationInterface::BACKORDERS_NO
                || $this->stockItemConfig->getBackorders() !== StockItemConfigurationInterface::BACKORDERS_NO
                && $this->stockItemConfig->getMinQty() < 0)
            && $productSalableQty > 0;
    }
}
