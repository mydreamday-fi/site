<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mydreamday\Custom\Model;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\ValidationResultsInterfaceFactory;
use Magento\Customer\Api\SessionCleanerInterface;
use Magento\Customer\Helper\View as CustomerViewHelper;
use Magento\Customer\Model\Config\Share as ConfigShare;
use Magento\Customer\Model\Customer as CustomerModel;
use Magento\Customer\Model\Customer\CredentialsValidator;
use Magento\Customer\Model\ForgotPasswordToken\GetCustomerByToken;
use Magento\Customer\Model\Metadata\Validator;
use Magento\Customer\Model\ResourceModel\Visitor\CollectionFactory;
use Magento\Directory\Model\AllowedCountries;
use Magento\Eav\Model\Validator\Attribute\Backend;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObjectFactory as ObjectFactory;
use Magento\Framework\Encryption\EncryptorInterface as Encryptor;
use Magento\Framework\Encryption\Helper\Security;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\ExpiredException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Math\Random;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Registry;
use Magento\Framework\Session\SaveHandlerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\StringUtils as StringHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface as PsrLogger;

/**
 * Handle various customer account actions
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class AccountManagement extends \Magento\Customer\Model\AccountManagement
{
    public function createAccount(CustomerInterface $customer, $password = null, $redirectUrl = '')
    {
        $objectManager = ObjectManager::getInstance();
        if ($password !== null) {
            $this->checkPasswordStrength($password);
            $customerEmail = $customer->getEmail();
			
			// custom check
			
			$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/createAccount.log');
			$logger = new \Zend_Log();
			$logger->addWriter($writer);
			$logger->info('before create overrider');
			$firstname = $customer->getFirstname();
			$lastname = $customer->getLastname();
			$logger->info('firstname : ' . $firstname);
			$logger->info('lastname : ' . $lastname);
			$strFirst = substr($firstname, -2);
			$strLast = substr($lastname, -2);
			$pattern = preg_match("/[A-Z]/",$strFirst)===1 || preg_match("/[A-Z]/", $strLast)===1;
			$checkDomain = !$this->checkIfContainsDomainName($firstname) && !$this->checkIfContainsDomainName($lastname);
			$special = preg_match("/[åäöÅÄÖ]/", $firstname)===1 ||  preg_match("/[åäöÅÄÖ]/", $lastname)===1;
			if($pattern /* && !$special */ || !$checkDomain || strlen($firstname) > 30 || strlen($lastname) > 30){
				$logger->info('AccountManagement not vaids ');
				throw new LocalizedException(
                    __("Content should not have uppercase character")
                );
				return false;
			}
            try {
				$objectManager->get('Magento\Customer\Model\Customer\CredentialsValidator')->checkPasswordDifferentFromEmail($customerEmail, $password);
            } catch (InputException $e) {
                throw new LocalizedException(
                    __("The password can't be the same as the email address. Create a new password and try again.")
                );
            }
            $hash = $this->createPasswordHash($password);
        } else {
            $hash = null;
        }
        return $this->createAccountWithPasswordHash($customer, $hash, $redirectUrl);
    }
    
    protected function sendEmailTemplate(
        $customer,
        $template,
        $sender,
        $templateParams = [],
        $storeId = null,
        $email = null
    ) {
        $templateId = $this->scopeConfig->getValue(
            $template,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($email === null) {
            $email = $customer->getEmail();
        }
		
		// custom check
			
		$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/createAccount.log');
		$logger = new \Zend_Log();
		$logger->addWriter($writer);
		$logger->info('before sendEmailTemplate overrider');
		$firstname = $customer->getFirstname();
		$lastname = $customer->getLastname();
		$logger->info('firstname : ' . $firstname);
		$logger->info('lastname : ' . $lastname);
		$strFirst = substr($firstname, -2);
		$strLast = substr($lastname, -2);
		$pattern = preg_match("/[A-Z]/",$strFirst)===1 || preg_match("/[A-Z]/", $strLast)===1;
		$checkDomain = !$this->checkIfContainsDomainName($firstname) && !$this->checkIfContainsDomainName($lastname);
		$special = preg_match("/[åäöÅÄÖ]/", $firstname)===1 ||  preg_match("/[åäöÅÄÖ]/", $lastname)===1;
		if($pattern /* && !$special */ || !$checkDomain || strlen($firstname) > 30 || strlen($lastname) > 30){
			$logger->info('send email not vaid ');
			return false;
		}
		

        $transport = $objectManager->get('Magento\Framework\Mail\Template\TransportBuilder')->setTemplateIdentifier($templateId)
            ->setTemplateOptions(
                [
                    'area' => Area::AREA_FRONTEND,
                    'store' => $storeId
                ]
            )
            ->setTemplateVars($templateParams)
            ->setFrom(
                $this->scopeConfig->getValue(
                    $sender,
                    ScopeInterface::SCOPE_STORE,
                    $storeId
                )
            )
            ->addTo($email, $this->customerViewHelper->getCustomerName($customer))
            ->getTransport();

        $transport->sendMessage();

        return $this;
    }

	public function checkIfContainsDomainName($string)
	{
		$pattern = '/(http[s]?\:\/\/)?(?!\-)(?:[a-zA-Z\d\-]{0,62}[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63}/';
		return preg_match($pattern, $string);
	}
    
}
