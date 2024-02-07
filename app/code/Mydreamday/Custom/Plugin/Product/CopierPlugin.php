<?php
namespace Mydreamday\Custom\Plugin\Product;

class CopierPlugin
{
    public function aroundCopy(
        \Magento\Catalog\Model\Product\Copier $subject,
        \Closure $proceed,
        \Magento\Catalog\Model\Product $product
    ) {
        /** @var \Magento\Catalog\Model\Product $duplicate */
        $duplicate = $proceed($product);

        $metadataPoolProperty = new \ReflectionProperty(get_class($subject), 'metadataPool');
        $metadataPoolProperty->setAccessible(true);
        $metadataPool = $metadataPoolProperty->getValue($subject);

        $metadata = $metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class);
        $product->getResource()->duplicate(
            $product->getData($metadata->getLinkField()),
            $duplicate->getData($metadata->getLinkField())
        );

        return $duplicate;
    }
}
