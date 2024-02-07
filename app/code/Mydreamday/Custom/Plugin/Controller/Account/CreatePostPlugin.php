<?php

namespace Mydreamday\Custom\Plugin\Controller\Account;

class CreatePostPlugin
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
		\Magento\Framework\Message\ManagerInterface $messageManager,
		\Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Framework\UrlInterface $url
	){
        $this->messageManager = $messageManager;
		$this->responseFactory = $responseFactory;
        $this->url = $url;
    }
    public function beforeExecute(\Magento\Customer\Controller\Account\CreatePost $subject)
    {
		$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/test.log');
		$logger = new \Zend_Log();
		$logger->addWriter($writer);
		$logger->info('before create');
        $firstname = $subject->getRequest()->getParam('firstname');
        $lastname = $subject->getRequest()->getParam('lastname');
		$logger->info('firstname :' . $firstname);
		$logger->info('lastname :' . $lastname);
		$strFirst = substr($firstname, -2);
		$strLast = substr($lastname, -2);
		$pattern = preg_match("/[A-Z]/",$strFirst)===1 || preg_match("/[A-Z]/", $strLast)===1;
		$checkDomain = !$this->checkIfContainsDomainName($firstname) && !$this->checkIfContainsDomainName($lastname);
		$special = preg_match("/[åäöÅÄÖ]/", $firstname)===1 ||  preg_match("/[åäöÅÄÖ]/", $lastname)===1;
		if($pattern /* && !$special */ || !$checkDomain || strlen($firstname) > 15 || strlen($lastname) > 15){
			$logger->info('check validd');
			$this->messageManager->addError(__('Content should not have uppercase character'));
			$redirectionUrl = $this->url->getUrl('customer/account/create');
			return $this->responseFactory->create()->setRedirect($redirectionUrl)->sendResponse();
		}
		$logger->info('validd ok');
    }
	
	public function checkIfContainsDomainName($string)
	{
		$pattern = '/(http[s]?\:\/\/)?(?!\-)(?:[a-zA-Z\d\-]{0,62}[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63}/';
		return preg_match($pattern, $string);
	}
}
