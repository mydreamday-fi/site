<?php
namespace David\PostNord\Model\Carrier;

use David\PostNord\Logger\Logger;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Item;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Psr\Log\LoggerInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;

class Postnord extends AbstractCarrier implements CarrierInterface
{
    protected $_code = 'postnord';

    protected $rateResultFactory;

    protected $rateMethodFactory;
    
    protected $helper;

    protected $logger;

    protected $backendQuoteSession;

    protected $cart;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        \David\PostNord\Helper\Data $helper,
        \Magento\Framework\Registry $registry,
        Logger $pnLogger,
        \Magento\Backend\Model\Session\Quote $backendQuoteSession,
        Cart $cart,
        array $data = []
    )
    {
        $this->rateResultFactory    = $rateResultFactory;
        $this->rateMethodFactory    = $rateMethodFactory;
        $this->helper               = $helper;
        $this->cart                 = $cart;
        $this->registry             = $registry;
        $this->logger               = $pnLogger;
        $this->backendQuoteSession  = $backendQuoteSession;

        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    public function getAllowedMethods()
    {
        return ['postnord' => $this->getConfigData('name')];
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }
        $quote = $this->getQuoteSafely($request);
        if ($quote === false) {
            return false;
        }
        $cartValue = $request->getPackageValueWithDiscount();
        if($cartValue < $this->getConfigData('minimum')){
            return false;
        }

        /** @var Result $result */
        $result = $this->rateResultFactory->create();

        $address = empty($quote->getShippingAddress()->getCountryId())
            ? $this->backendQuoteSession->getQuote()->getShippingAddress()
            : $quote->getShippingAddress();
        $data = [];
        if (null !== $this->registry->registry('postnord_customdata')) {
            $data = $this->registry->registry('postnord_customdata');
            $this->registry->unregister('postnord_customdata');
        }

        $pickupLocation = $this->helper->getPickupLocation($address);

        $amount = $this->getConfigData('price');
        $minimum = $this->getConfigData('cart_price');
        $new_price = $this->getConfigData('new_price');
        if ($cartValue && $minimum && $cartValue >= $minimum) {
            $amount = $new_price;
        }
        $shippingPrice = $this->getFinalPriceWithHandlingFee($amount);
        if (!empty($pickupLocation)) {
            foreach ($pickupLocation as $pickup){
                $data[$pickup['servicePointId']]['distance'] = $pickup['distanceHtml'];
                $data[$pickup['servicePointId']]['rawDistance'] = $pickup['routeDistance'];
                $data[$pickup['servicePointId']]['address']  = $pickup['address'];
                $method = $this->rateMethodFactory->create();
                $method->setCarrier('postnord');
                $method->setCarrierTitle(str_replace("Pn","PostNord", $pickup['name']));
                $method->setMethod($pickup['servicePointId']);
                $method->setMethodTitle($this->getConfigData('title'));
                $method->setPrice($shippingPrice);
                $method->setCost($amount);
                
                $result->append($method);
            }
        } else {
            // When there are no pickup points available, add a method with a custom attribute
            $method = $this->rateMethodFactory->create();
            $method->setCarrier('postnord');
            $method->setCarrierTitle(__('Check postal code - We could not find any pick-up points'));
            $method->setMethod('no_pickuppoints');
            $method->setMethodTitle(__('PostNord Noutopiste - Check postal code'));
            $method->setPrice($shippingPrice);
            $method->setCost(0);
            $result->append($method);
        }

        $this->registry->register('postnord_customdata', $data);
        return $result;
    }

    public function isTrackingAvailable(): bool
    {
        return true;
    }

    /**
     * @param RateRequest $request
     * @return false|Quote
     */
    protected function getQuoteSafely(RateRequest $request)
    {
        $items = $request->getAllItems();
        if (empty($items)) {
            return false;
        }

        /** @var Item $firstItem */
        $firstItem = reset($items);
        if (!$firstItem) {
            return false;
        }

        $quote = $firstItem->getQuote();
        if (!($quote instanceof Quote)) {
            return false;
        }

        return $quote;
    }
}