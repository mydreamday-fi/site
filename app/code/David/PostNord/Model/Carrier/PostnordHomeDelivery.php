<?php
namespace David\PostNord\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Psr\Log\LoggerInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;

class PostnordHomeDelivery extends AbstractCarrier implements CarrierInterface
{
    protected $_code = 'postnord_homedelivery';

    protected $rateResultFactory;

    protected $rateMethodFactory;

    protected $helper;

    protected $checkoutSession;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        \David\PostNord\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    )
    {
        $this->rateResultFactory    = $rateResultFactory;
        $this->rateMethodFactory    = $rateMethodFactory;
        $this->helper               = $helper;
        $this->checkoutSession      = $checkoutSession;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    public function getAllowedMethods()
    {
        return ['postnord_homedelivery' => $this->getConfigData('name')];
    }

    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }
        $cartValue = $request->getPackageValueWithDiscount();
        if($cartValue < $this->getConfigData('minimum')){
            return false;
        }

        $result = $this->rateResultFactory->create();
        $method = $this->rateMethodFactory->create();
        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));
        $method->setMethod($this->_code);
        $method->setMethodTitle($this->getConfigData('name'));

        $amount = $this->getConfigData('price');

        $minimum = $this->getConfigData('cart_price');
        $new_price = $this->getConfigData('new_price');
        if ($cartValue && $minimum && $cartValue >= $minimum) {
            $amount = $new_price;
        }
        
        $shippingPrice = $this->getFinalPriceWithHandlingFee($amount);
        $method->setPrice($shippingPrice);
        $method->setCost($amount);
        
        $result->append($method);

        return $result;
    }

    public function isTrackingAvailable(): bool
    {
        return true;
    }
}