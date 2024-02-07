<?php

namespace Mydreamday\Hotfixes\Service\Mirasvit;

use Mirasvit\SeoFilter\Api\Data\RewriteInterface;
use Mydreamday\Hotfixes\Model\Mirasvit\ConfigProvider;

class RewriteService extends \Mirasvit\SeoFilter\Service\RewriteService
{
    /**
     * Overrides base Mirasvit method to ensure the color attribute is properly cast to an int
     */
    public function getOptionRewrite(string $attributeCode, string $filterValue, ?int $storeId = null, bool $useCache = true): ?RewriteInterface
    {
        if ($attributeCode == ConfigProvider::FILTER_COLOR) {
            $filterValue = is_numeric($filterValue) ? $filterValue : '1';
        }

        return parent::getOptionRewrite($attributeCode, $filterValue, $storeId, $useCache);
    }
}
