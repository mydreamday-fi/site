<?php

namespace Meetanshi\PayshipRestriction\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Meetanshi\PayshipRestriction\Model\ResourceModel\PayshipRestriction\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class ApiData
 */
class ApiData extends AbstractHelper
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManagerInterface;

    /**
     * @var
     */
    protected $helper;

    /**
     * ApiData constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory $collectionFactory
     * @param Data $data
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        CollectionFactory $collectionFactory,
        Data $data
    ) {

        $this->storeManagerInterface = $storeManager;
        $this->collectionFactory = $collectionFactory;
        $this->helper = $data;
        parent::__construct($context);
    }

    /**
     * @return mixed|array
     */
    public function getAllMethods($type, $websiteId)
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('type', ['eq' => $type]);
        $collection->addFieldToFilter('website_id', ['eq' => $websiteId]);
        return $collection->load();
    }

    /**
     * @param $data
     * @return array|string
     */
    public function getMethods($data)
    {
        $flag = 'false';
        $availableMethod = [];
        $paymentMethods = [];
        $shipmentMethods = [];
        $availableMethods = [];
        $methodType = ['payment', 'shipping'];

        try {
            if ($this->helper->isEnabled()) {
                if (isset($data['type']) and isset($data['customerGroup']) and isset($data['websiteId'])) {
                    $type = $data['type'];
                    $customerGroup = $data['customerGroup'];
                    $websiteId = $data['websiteId'];

                    if (!in_array($type, $methodType)) {
                        $availableMethods = 'Invalid type, it must be payment or shipping.';
                        $response = ['success' => 'false',
                            'methods' => $availableMethods
                        ];
                        return $response;
                    }

                    $collection = $this->getAllMethods($type, $websiteId);
                    if (sizeof($collection) > 0) {
                        foreach ($collection as $methods) {
                            if ($methods->getRestrictionId()) {
                                if ($methods->getCustomerGroupIds() != '') {
                                    $allowedGroups = explode(',', $methods->getCustomerGroupIds());
                                    if (in_array($customerGroup, $allowedGroups)) {
                                        $availableMethod[] = $methods->getMethod();
                                        $flag = 'true';
                                    }
                                }
                            }
                        }
                        if ($type == 'payment') {
                            $payments = $this->helper->getActivePaymentMethods($websiteId);
                            if (sizeof($payments) > 0) {
                                foreach ($payments as $paymentCode => $paymentModel) {
                                    $paymentTitle = $this->helper->scopeConfig->getValue('payment/' . $paymentCode . '/title', ScopeInterface::SCOPE_STORE);
                                    $paymentMethods[$paymentCode] = [
                                        'title' => $paymentTitle,
                                        'value' => $paymentCode
                                    ];
                                }
                                foreach ($paymentMethods as $key => $value) {
                                    if (in_array($key, $availableMethod)) {
                                        $availableMethods[] = ['code' => $key, 'title' => $value['title']];
                                    }
                                }
                            } else {
                                $flag = 'false';
                                $availableMethods = 'No payment method activated yet.';
                            }
                        } else {
                            $shipments = $this->helper->getActiveShippingMethods($websiteId);
                            if (sizeof($shipments) > 0) {
                                foreach ($shipments as $shipmentCode => $shipmentModel) {
                                    $shipmentTitle = $this->helper->scopeConfig->getValue('carriers/' . $shipmentCode . '/title', ScopeInterface::SCOPE_STORE);
                                    $shipmentMethods[$shipmentCode] = [
                                        'title' => $shipmentTitle,
                                        'value' => $shipmentCode
                                    ];
                                }
                                foreach ($shipmentMethods as $key => $value) {
                                    if (in_array($key, $availableMethod)) {
                                        $availableMethods[] = ['code' => $key, 'title' => $value['title']];
                                    }
                                }
                            } else {
                                $flag = 'false';
                                $availableMethods = 'No shipping method activated yet.';
                            }
                        }
                    } else {
                        $flag = 'false';
                        $availableMethods = 'No method(s) available in website';
                    }
                }
                $response = ['success' => $flag,
                    'methods' => $availableMethods
                ];
            } else {
                $response = ['success' => 'false',
                    'methods' => 'No method(s) available, module is disabled'
                ];
            }
            return $response;
        } catch (\Exception $e) {
            $response = ['success' => 'false',
                'methods' => $e->getMessage()
            ];
            return $response;
        }
    }
}
