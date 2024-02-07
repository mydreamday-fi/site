<?php
namespace David\PostNord\Observer;

use Magento\Checkout\Model\Cart;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class OrderObserver implements ObserverInterface
{
    /**
     * custom event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    protected $dataHelper;
    protected $_order;

    public function __construct(
        OrderInterface $order,
        LoggerInterface $logger,
        Cart $cart,
        \Magento\Backend\Model\Session\Quote $backendQuoteSession,
        \David\PostNord\Helper\Data $dataHelper
    ) {
        $this->_order = $order;
        $this->logger = $logger;
        $this->cart = $cart;
        $this->backendQuoteSession = $backendQuoteSession;
        $this->dataHelper = $dataHelper;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        try {
            $quote = $this->cart->getQuote();
            /** @var Order $order */
            $order = $observer->getOrder();
            $shipping_method_code = $quote->getShippingAddress()->getShippingMethod();
            if (!$shipping_method_code) {
                $shipping_method_code = $order->getShippingMethod();
            }
            if (!$shipping_method_code) {
                $shipping_method_code = $this->backendQuoteSession->getQuote()->getShippingAddress()->getShippingMethod();
                $quote = $this->backendQuoteSession->getQuote();
            }
            if ($this->dataHelper->isDebugMode()) {
                $this->logger->info(sprintf('Order %s quote shipping method: %s', $order->getIncrementId(), $quote->getShippingAddress()->getShippingMethod()));
                $this->logger->info(sprintf('Order %s order shipping method: %s', $order->getIncrementId(), $order->getShippingMethod()));
                $this->logger->info(sprintf('Order %s quote address: %s', $order->getIncrementId(), ($quote->getShippingAddress()->getPostcode())));
                $this->logger->info(sprintf('Order %s order address: %s', $order->getIncrementId(), ($order->getShippingAddress()->getPostcode())));
            }
            if ($this->dataHelper->isPostnord($shipping_method_code)) {
                $address = $order->getShippingAddress();
                $pickupLocation = $this->dataHelper->getPickupLocation($address);
                if ($this->dataHelper->isDebugMode()) {
                    $this->logger->info(sprintf('Order %s pickup locations: %s', $order->getIncrementId(), json_encode($pickupLocation)));
                }
                $pickupData = array();
                if ($pickupLocation && count($pickupLocation)){
                    foreach($pickupLocation as $pickup){
                        if ('postnord_' . $pickup['servicePointId'] == $shipping_method_code) {
                            $pickupData['servicePointId']   = $pickup['servicePointId'];
                            $pickupData['address']          = $pickup['only_address'];
                            $pickupData['distance']         = $pickup['distanceHtml'];
                            $pickupData['name']             = str_replace("Pn","PostNord",$pickup['name']);
                            $pickupData['city']             = $pickup['only_city'];
                            $pickupData['postcode']         = $pickup['only_postalCode'];
                            $pickupData['country']          = $pickup['only_countryCode'];
                        }
                    }
                }
                $order->setData('postnord_pickup', json_encode($pickupData));
            }
            
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }
}
