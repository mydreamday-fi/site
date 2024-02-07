<?php
namespace David\PostNord\Helper;

use David\PostNord\Logger\Logger;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Checkout\Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Information;
use Magento\Framework\App\Config\ScopeConfigInterface;
use GuzzleHttp\Client;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const SHIPPING_LABEL_URI = 'https://api2.postnord.com/rest/shipment/v3/edi/labels/pdf';
    const RETURN_LABEL_URI = '';

    const PICKUP_DEFAULT_POSTCODE = [
        'fi' => '00 100',
        'se' => '11 145'
    ];
    const PICKUP_DEFAULT_CITY = [
        'fi' => 'Helsinki',
        'se' => 'Stockholm'
    ];

    const ZIP_MIN_LENGTH = 5;


    protected $dir;
    protected $curl;
	protected $storeManager;
	protected $storeInfo;
    protected $scopeConfig;
    protected $orderRepository;
    protected $logger;

    public function __construct(
        DirectoryList $dir,
        Curl $curl,
		StoreManagerInterface $storeManager,
		Information $storeInfo,
		ScopeConfigInterface $scopeConfig,
        OrderRepositoryInterface $orderRepository,
        Logger $logger
    ) {
        $this->dir              = $dir;
        $this->curl             = $curl;
        $this->curl->setTimeout(15);
        $this->storeManager     = $storeManager;
        $this->storeInfo        = $storeInfo;
		$this->scopeConfig 		= $scopeConfig;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
    }

    public function getConfigValue($path, $storeId = null)
    {
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getDesOfPostnordPackCollect()
    {
        return $this->getConfigValue('carriers/postnord/description');
    }
    public function getDesOfPostnordHomDelivery()
    {
        return $this->getConfigValue('carriers/postnord_homedelivery/description');
    }

    public function getIconOfPostnordPackCollect()
    {
        return $this->getMediaPath().  $this->getConfigValue('carriers/postnord/icon');
    }
    public function getIconOfPostnordHomDelivery()
    {
        return $this->getMediaPath(). $this->getConfigValue('carriers/postnord_homedelivery/icon');
    }

    public function getMediaPath(){
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA ) . 'icon/';
    }

    public function getPostiKotipaketti()
    {
        return $this->getConfigValue('carriers/posti_kotipaketti/sort_order');
    }
    public function getPostiExpress()
    {
        return $this->getConfigValue('carriers/posti_expresspaketti/sort_order');
    }
    public function getPostiPickPoint()
    {
        return $this->getConfigValue('carriers/sort_pickuppoint/sort_order');
    }

    public function getPostNordHomeDelivery()
    {
        return $this->getConfigValue('carriers/postnord_homedelivery/sort_order');
    }

    public function getPostNord()
    {
        return $this->getConfigValue('carriers/postnord/sort_order');
    }

    public function isDebugMode(): bool
    {
        return $this->getConfigValue('postnord_config/debug/debug_mode') == 1;
    }

    public function isPostnord($code)
    {
        if (!empty($code) && (strpos($code, 'postnord') !== false || strpos($code, 'postnord') !== false)) {
            return true;
        } else {
            return false;
        }
    }

    public function isHomeDelivery($code)
    {
        if ($code == 'postnord_homedelivery_postnord_homedelivery') {
            return true;
        }
        return false;
    }

    public function getAPIKey(){
        return $this->getConfigValue('postnord_config/api/api_key');
    }

    public function getStoreName($storeId = null){
        return $this->getConfigValue('postnord_config/store/name', $storeId);
    }

    public function getStoreAddress($storeId = null){
        return $this->getConfigValue('postnord_config/store/address', $storeId);
    }

    public function getStoreCity($storeId = null){
        return $this->getConfigValue('postnord_config/store/city', $storeId);
    }

    public function getStoreCountry($storeId = null){
        return $this->getConfigValue('postnord_config/store/country', $storeId);
    }

    public function getStorePostcode($storeId = null){
        return $this->getConfigValue('postnord_config/store/postcode', $storeId);
    }

    public function validateZip($zip): bool
    {
        return is_string($zip) && strlen($zip) >= static::ZIP_MIN_LENGTH;
    }

    /**
     * @param $address
     *
     * @return array
     */
    public function getPickupLocation($address): array
    {
        $rates = [];
        // Strip commas as PostNord doesn't like address1 with commas in it
        $street = str_replace(',', '', $address->getStreet());
        $countryCode = mb_strtolower($address->getCountryId() ?? '');
        $defaultPostcode = self::PICKUP_DEFAULT_POSTCODE[$countryCode] ?? self::PICKUP_DEFAULT_POSTCODE['fi'];
        $postcode = empty($address->getPostcode()) ? $defaultPostcode : $address->getPostcode();
        $defaultCity = self::PICKUP_DEFAULT_CITY[$countryCode] ?? self::PICKUP_DEFAULT_CITY['fi'];;
        $city = empty($address->getCity()) ? $defaultCity : $address->getCity();

        if ($this->validateZip($postcode) < 5) {
            return [];
        }

        $params = [
            'returnType'            => 'json',
            'countryCode'           => $address->getCountryId(),
            'customerKey'           => $this->getAPIKey(),
            'city'                  => $city,
            'postalCode'            => $postcode,
            'streetName'            => $street,
            'streetNumber'          => '',
            'numberOfServicePoints' => 10,
            'srId'                  => 'EPSG:4326',
            'context'               => 'optionalservicepoint',
            'responseFilter'        => 'public',
            'apikey'                => $this->getAPIKey()
        ];

        if ($postcode && $city) {
            $url = "https://api2.postnord.com/rest/businesslocation/v5/servicepoints/nearest/byaddress?" . http_build_query($params);
            $curl = $this->curl;
            $curl->get($url);
            if ($data = json_decode($curl->getBody(), true)) {
                $servicePoints = $data['servicePointInformationResponse']['servicePoints'] ?? [];
                if (empty($servicePoints)) {
                    return [];
                }

                foreach($servicePoints as $pickup){
                    $rates[$pickup['servicePointId']]['servicePointId']    = $pickup['servicePointId'];
                    $rates[$pickup['servicePointId']]['name']              = $pickup['name'];
                    $rates[$pickup['servicePointId']]['routeDistance']     = $pickup['routeDistance'];
                    $rates[$pickup['servicePointId']]['distanceHtml']      = (float)$pickup['routeDistance'] >= 1000 ? number_format((float)$pickup['routeDistance'] / 1000, 1) . ' km' : $pickup['routeDistance'] . ' m';
                    $rates[$pickup['servicePointId']]['address']           = $pickup['deliveryAddress']['streetName'] . ' ' . $pickup['deliveryAddress']['streetNumber'] . ', ' . $pickup['deliveryAddress']['city'];
                    $rates[$pickup['servicePointId']]['only_address']      = $pickup['deliveryAddress']['streetName'] . ' ' . $pickup['deliveryAddress']['streetNumber'];
                    $rates[$pickup['servicePointId']]['only_city']         = $pickup['deliveryAddress']['city'];
                    $rates[$pickup['servicePointId']]['only_postalCode']   = $pickup['deliveryAddress']['postalCode'];
                    $rates[$pickup['servicePointId']]['only_countryCode']  = $pickup['deliveryAddress']['countryCode'];
                }

                usort($rates, function ($a, $b) {
                    $a['routeDistance'] ?? 0 <=> $b['routeDistance'] ?? 0;
                });
            }
        }

        return $rates;
    }

    /**
     * @param $orderId
     *
     * @return array|null
     * @throws LocalizedException
     */
    public function generateShippingLabel($orderId): ?array
    {
        $order = $this->orderRepository->get($orderId);
        if (empty($order)) {
            $this->logger->warning(sprintf('Could not find order with ID %s', $orderId));

            return null;
        }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $storeId = $order->getStoreId();
        $method     = $order->getShippingMethod();
        $store      = $objectManager->create('Magento\Store\Model\Store');
        $storeLocale = $objectManager->get('Magento\Framework\Locale\Resolver');
        $lang = strstr($storeLocale->getLocale(), '_', true);
        $storeInfo = current((array)$this->storeInfo->getStoreInformationObject($store));
        $address = $order->getShippingAddress();
        $params = [
            'apikey'        => $this->getAPIKey(),
            'paperSize'     => 'A5',
            'rotate'        => 0,
            'multiPDF'      => false,
            'labelType'     => 'standard',
            'pnInfoText'    => false,
            'labelsPerPage' => 100,
            'page'          => 1,
            'processOffline'=> false,
            'storeLabel'    => false,
            'locale'        => $lang
        ];

        $items = $order->getAllVisibleItems();

        $weight = 0;
        foreach($items as $item) {
            $weight += ($item->getWeight() * $item->getQty()) ;
        }

        $serviceNumber = $this->isHomeDelivery($method) ? '17' : '19';

        $contactName = '';
        $consignorAddress = $storeInfo['street_line1'];
        $consignorCity = $storeInfo['city'];
        $consignorPostcode = $storeInfo['postcode'];
        $consignorCountry = 'FI';
        $servicePointId = '';
        if($order->getPostnordPickup()){
            $dataPickup = json_decode($order->getPostnordPickup());
            if(isset($dataPickup->servicePointId)){
                $servicePointId = $dataPickup->servicePointId;
            }
            if(isset($dataPickup->name)){
                $contactName = $dataPickup->name;
            }
            if(isset($dataPickup->address)){
                $consignorAddress = $dataPickup->address;
            }
            if(isset($dataPickup->city)){
                $consignorCity = $dataPickup->city;
            }
            if(isset($dataPickup->postcode)){
                $consignorPostcode = $dataPickup->postcode;
            }
            if(isset($dataPickup->country)){
                $consignorCountry = $dataPickup->country;
            }
        }
        $additionForPhoneNumber = $address->getCountryId() == 'FI' ? '358' : '46';
        $applicationId = 1468;
        $customerPhoneValid = substr($address->getTelephone(),0,1) == 0 ? $additionForPhoneNumber . substr($address->getTelephone(), 1): $address->getTelephone();

        $additionalServiceCode = [];
        if ($this->isHomeDelivery($method)) {
            $additionalServiceCode[] = 'C7';
        } else {
            $additionalServiceCode = ['A3', 'A4', 'A7'];
            if ($address->getCountryId() == 'FI') {
                $additionalServiceCode[] = 'M7';
            }
        }

        $payload = [
            'messageDate' => date('Y-m-d\TH:i:s\Z'),
            'messageFunction' => 'Instruction',
            'messageId' => '0',
            'application' => [
                'applicationId' => $applicationId,
                'name' => 'Booking FI',
                'version' => '1.0',
            ],
            'updateIndicator' => 'Original',
            'shipment' => [
                0 => [
                    'shipmentIdentification' => [
                        'shipmentId' => '0',
                    ],
                    'dateAndTimes' => [
                        'loadingDate' => date(DATE_ISO8601),
                    ],
                    'service' => [
                        'basicServiceCode' => $serviceNumber,
                        'additionalServiceCode' => $additionalServiceCode,
                    ],
                    'numberOfPackages' => [
                        'value' => 1,
                    ],
                    'parties' => [
                        'consignor' => [
                            'issuerCode' => 'Z14',
                            'partyIdentification' => [
                                'partyId' => '20821270',
                                'partyIdType' => '160',
                            ],
                            'party' => [
                                'nameIdentification' => [
                                    'companyName' => $this->getStoreName($storeId),
                                ],
                                'address' => [
                                    'streets' => [
                                        0 => $this->getStoreAddress($storeId),
                                    ],
                                    'postalCode' => $this->getStorePostcode($storeId),
                                    'city' => $this->getStoreCity($storeId),
                                    'countryCode' => $this->getStoreCountry($storeId),
                                ],
                            ],
                        ],
                        'consignee' => [
                            'party' => [
                                'nameIdentification' => [
                                    'name' => $address->getFirstname(). ' ' . $address->getLastname(),
                                ],
                                'address' => [
                                    'streets' => [
                                        0 => implode(" ",$address->getStreet()),
                                    ],
                                    'postalCode' => $address->getPostcode(),
                                    'city' => $address->getCity(),
                                    'countryCode' => $address->getCountryId(),
                                ],
                                'contact' => [
                                    'contactName' => $address->getFirstname(). ' ' . $address->getLastname(),
                                    'emailAddress' => $order->getCustomerEmail(),
                                    'phoneNo' => '+' . $customerPhoneValid,
                                    'smsNo' => '+' . $customerPhoneValid,
                                ],
                            ],
                        ],
                        'deliveryParty' => [
                            'partyIdentification' => [
                                'partyId' => $servicePointId,
                                'partyIdType' => '156',
                            ],
                            'party' => [
                                'nameIdentification' => [
                                    'name' => $contactName
                                ],
                                'address' => [
                                    'streets' => [
                                        0 => $consignorAddress,
                                    ],
                                    'postalCode' => $consignorPostcode,
                                    'city' => $consignorCity,
                                    'countryCode' => $consignorCountry,
                                ],
                            ],
                        ],
                    ],
                    'goodsItem' => [
                        0 => [
                            'packageTypeCode' => 'PC',
                            'items' => [
                                0 => [
                                    'itemIdentification' => [
                                        'itemId' => '0',
                                        'itemIdType' => 'SSCC',
                                    ],
                                    'grossWeight' => [
                                        'value' => $weight,
                                        'unit' => 'KGM',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        if ($this->isHomeDelivery($method)) {
            unset($payload['shipment'][0]['parties']['deliveryParty']);
        }
        $jsonPayload = json_encode($payload);
        try {
            $client = new Client();
            $options = [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept: application/json'
                ],
                'body' => $jsonPayload,
                'query' => $params
            ];

            if ($this->isDebugMode()) {
                $this->logger->info(sprintf('URI: %s?%s', self::SHIPPING_LABEL_URI, http_build_query($params)));
                $this->logger->info(sprintf('Request body: %s', $jsonPayload));
            }

            $response = $client->post(self::SHIPPING_LABEL_URI, $options);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            $this->logger->warning(sprintf('Failed to generate shipping label. ERROR: %s', $e->getResponse()->getBody()));

            throw new LocalizedException(
                __($e->getMessage())
            );
        }
    }

    /**
     * @param $orderId
     *
     * @return array|null
     */
    public function generateReturnLabel($orderId): ?array
    {
        $order = $this->orderRepository->get($orderId);
        $pnData = json_decode($order->getData('postnord_data'), true);
        if (empty($pnData['bookingResponse']['bookingId'])) {
            return null;
        }
        $bookingId = $pnData['bookingResponse']['idInformation'][0]['ids'][0]['value'] ?? false;
        if (empty($bookingId)) {
            return null;
        }

        $params = [
            'apikey'                => $this->getAPIKey(),
            'mirrorReferences'      => 'true',
            'appTracking'           => 'true',
            'deliveryNotification'  => 'false',
            'qrCodeScale'           => '4',
            'paperSize'             => 'A5',
            'rotate'                => 0,
            'multiPDF'              => 'false',
            'labelType'             => 'standard',
            'pnInfoText'            => 'false',
            'labelsPerPage'         => 100,
            'page'                  => 1,
            'processOffline'        => 'false',
            'emailQRcodeTo'         => 'false',
            'smsQRcodeTo'           => 'false'
        ];

        $jsonData = '[
            {
                "return": {
                    "id": "'. $bookingId .'"
                }
            }
        ]';

        $url = "https://api2.postnord.com/rest/shipment/v3/returns/ids/labels/pdf?".  http_build_query($params);;
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        $result = curl_exec($curl);
        $this->logger->info(sprintf('Return label response for order %s: %s', $orderId, $result));

        return json_decode($result, true);
    }

    public function getPdfFile($order,$postnordData,$type = 'shipment'){
        if(!@$postnordData->bookingResponse->bookingId) return false;
        $fileName = 'postnord_' . $type. '_' . $order->getId() . '.pdf';
        $path = $this->dir->getPath('media') . "/pdf/" . $fileName;
        if(!file_exists($path)){
            $pdf_content = $postnordData->labelPrintout[0]->printout->data;
            $pdf_decoded = base64_decode ($pdf_content);
            $pdf = fopen ($path,'wb');
            fwrite ($pdf,$pdf_decoded);
            //close output file
            fclose ($pdf);
        }
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'pdf/' . $fileName;
    }

    public function getPostnordData($order){
        $orderData = $order->getData('postnord_data');
        if (empty($orderData)) return false;
        $postnordData = json_decode($orderData);
        if(!@$postnordData->bookingResponse->bookingId) return false;
        return $postnordData;
    }

    public function getRefundPostnordData($order){
        $orderData = $order->getData('postnord_refund_data');
        if (empty($orderData)) return false;
        $postnordData = json_decode($orderData);
        if(!@$postnordData->bookingResponse->bookingId) return false;
        return $postnordData;
    }
}
