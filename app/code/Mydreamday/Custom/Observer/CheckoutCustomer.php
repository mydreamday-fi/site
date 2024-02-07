<?php
namespace Mydreamday\Custom\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\Serializer\Json;

class CheckoutCustomer implements ObserverInterface
{

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;


    protected $_request;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\LayoutInterface $layout
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\App\RequestInterface $request,
        Json $serializer = null
    ) {
        $this->_storeManager = $storeManager;
        $this->_layout = $layout;
        $this->_request = $request;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
    }

    /**
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $customer = $observer->getCustomer();
		$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/createAccountsuccess.log');
		$logger = new \Zend_Log();
		$logger->addWriter($writer);
		$firstname = $customer->getFirstname();
		$lastname = $customer->getLastname();
		$logger->info('firstname : ' . $firstname);
		$logger->info('lastname : ' . $lastname);
		$strFirst = substr($firstname, -2);
		$strLast = substr($lastname, -2);
		$pattern = preg_match("/[A-Z]/",$strFirst)===1 || preg_match("/[A-Z]/", $strLast)===1;
		$checkDomain = !$this->checkIfContainsDomainName($firstname) && !$this->checkIfContainsDomainName($lastname);
		$special = preg_match("/[åäöÅÄÖ]/", $firstname)===1 ||  preg_match("/[åäöÅÄÖ]/", $lastname)===1;
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
       
		if($pattern /* && !$special */ || !$checkDomain || strlen($firstname) > 30 || strlen($lastname) > 30){
			$logger->info('name not valids :' . $customer->getEmail());
			$customer = $objectManager->create('Magento\Customer\Model\ResourceModel\CustomerRepository')->deleteById($customer->getId());
		}
    }
	
	public function checkIfContainsDomainName($string)
	{
		$pattern = '/(http[s]?\:\/\/)?(?!\-)(?:[a-zA-Z\d\-]{0,62}[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63}/';
		return preg_match($pattern, $string);
	}
}
