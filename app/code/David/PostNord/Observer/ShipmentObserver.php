<?php
namespace David\PostNord\Observer;

use David\PostNord\Helper\Data;
use David\PostNord\Logger\Logger;
use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Shipment\TrackFactory;

class ShipmentObserver implements ObserverInterface
{
    /** @var Data  */
    protected $dataHelper;

    /** @var OrderRepositoryInterface  */
    protected $orderRepository;

    /** @var Logger  */
    protected $logger;

    /** @var TrackFactory  */
    protected $trackFactory;

    public function __construct(
        Data $dataHelper,
        OrderRepositoryInterface $orderRepository,
        Logger $logger,
        TrackFactory $trackFactory
    ) {
        $this->dataHelper = $dataHelper;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
        $this->trackFactory = $trackFactory;
    }

    /**
     * Observer on Shipment save to generate a shipping label from PostNord and append the tracking details to the shipment
     *
     * @param Observer $observer
     *
     * @returns void
     */
    public function execute(Observer $observer)
    {
        try {
            $shipment = $observer->getEvent()->getShipment();
            $shippingMethod = $shipment->getOrder()->getData('shipping_method');

            if ($this->dataHelper->isPostnord($shippingMethod)) {
                if (count($shipment->getAllTracks()) == 0) {
                    $orderId = $shipment->getOrder()->getId();
                    $order = $this->orderRepository->get($orderId);

                    // Generate shipping label
                    $postNordResponse = $this->dataHelper->generateShippingLabel($orderId);
                    if(is_array($postNordResponse) && array_key_exists('bookingResponse', $postNordResponse)) {
                        $order->setData('postnord_data', json_encode($postNordResponse));
                    } else {
                        $this->logger->warning(sprintf('Failed to generate shipment label for order %. Response: %s',
                            $orderId,
                            json_encode($postNordResponse)
                        ));
                    }

                    // Generate return label - it is placed in the parcel
                    $postNordReturnResponse = $this->dataHelper->generateReturnLabel($orderId);
                    if(is_array($postNordReturnResponse) && array_key_exists('bookingResponse', $postNordReturnResponse)) {
                        $order->setData('postnord_refund_data', json_encode($postNordReturnResponse));
                    } else {
                        $this->logger->warning(sprintf('Failed to generate return label for order %. Response: %s',
                            $orderId,
                            json_encode($postNordReturnResponse)
                        ));
                    }

                    $name = $this->dataHelper->isHomeDelivery($shippingMethod)
                        ? 'Postnord Home Delivery'
                        : 'Postnord MyPack Collect';

                    $trackingData = [
                        'carrier_code' => $shippingMethod,
                        'title' => $name,
                        'number' => $postNordResponse['bookingResponse']['idInformation'][0]['ids'][0]['value']
                    ];
                    $track = $this->trackFactory->create()->addData($trackingData);
                    $shipment->addTrack($track);
                    $this->logger->info('Tracking info generated successfully for order ' . $orderId);
                }
            }
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }
}