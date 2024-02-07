<?php

namespace Meetanshi\PayshipRestriction\Model\Api;

use Meetanshi\PayshipRestriction\Helper\ApiData;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class CustomerGroup
 */
class CustomerGroup
{
    /**
     * @var ApiData
     */
    protected $helper;
    /**
     * @var
     */
    protected $storeManager;

    /**
     * @param ApiData $helper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ApiData $helper,
        StoreManagerInterface $storeManager
    ) {
        $this->helper = $helper;
    }

    /**
     * @inheritdoc
     */

    public function getPost($type, $customerGroup, $websiteId)
    {
        $data = [
            "type" => $type,
            "customerGroup" => $customerGroup,
            "websiteId" => $websiteId
             ];

        $response = $this->helper->getMethods($data);
        $returnArr = json_encode($response);
        return $returnArr;
    }
}
