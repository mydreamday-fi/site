<?php

namespace Meetanshi\PayshipRestriction\Api;

interface CustomerGroupInterface
{
    /**
     * GET for Post api
     * @param string $type
     * @param string $customerGroup
     * @param string $websiteId
     * @return string
     */

    public function getPost($type, $customerGroup, $websiteId);
}
