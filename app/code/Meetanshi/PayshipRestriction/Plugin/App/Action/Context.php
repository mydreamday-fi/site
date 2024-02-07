<?php

namespace Meetanshi\PayshipRestriction\Plugin\App\Action;

use Meetanshi\PayshipRestriction\Model\Customer\Context as CustomerContext;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;

/**
 * Class Context
 */
class Context
{
    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var HttpContext
     */
    protected $httpContext;

    /**
     * Context constructor.
     * @param CustomerSession $customerSession
     * @param HttpContext $httpContext
     */
    public function __construct(
        CustomerSession $customerSession,
        HttpContext $httpContext
    ) {
        $this->customerSession = $customerSession;
        $this->httpContext = $httpContext;
    }

    
    /**
     * @param ActionInterface $subject
     * @param \Closure $proceed
     * @param RequestInterface $request
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundDispatch(
        ActionInterface $subject,
        \Closure $proceed,
        RequestInterface $request
    ) {
        $customerId = $this->customerSession->getCustomerId();
        if (!$customerId) {
            $groupId = 0;
        } else {
            $groupId = $this->customerSession->getCustomerGroupId();
        }

        $this->httpContext->setValue(
            CustomerContext::CONTEXT_CUSTOMER_ID,
            $groupId,
            false
        );

        return $proceed($request);
    }
}
