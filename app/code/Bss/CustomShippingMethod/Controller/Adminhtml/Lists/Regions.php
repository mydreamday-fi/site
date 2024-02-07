<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CustomShippingMethod
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomShippingMethod\Controller\Adminhtml\Lists;

/**
 * Class Region list
 *
 */
class Regions extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $countryFactory;
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $jsonResultFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
    ) {
        $this->countryFactory = $countryFactory;
        $this->jsonResultFactory = $jsonResultFactory;
        parent::__construct($context);
    }
    /**
     * Default customer account page
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $countryCode = $this->getRequest()->getParam('country');
        $state = '';
        if ($countryCode != '') {
            $stateArray =$this->countryFactory->create()->setId($countryCode)
                ->getLoadedRegionCollection()->toOptionArray();
            if (count($stateArray) > 0) {
                foreach ($stateArray as $_state) {
                    if ($_state['value']) {
                        $state .= "<option value= '" . $_state['label'] . "' >" . $_state['label'] . "</option>";
                    }
                }
            }
        }
        $data['htmlContent'] = $state;
        $result = $this->jsonResultFactory->create();
        $result->setData($data);
        return $result;
    }
}
