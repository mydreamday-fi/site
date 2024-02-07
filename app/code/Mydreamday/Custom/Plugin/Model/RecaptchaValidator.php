<?php
namespace Mydreamday\Custom\Plugin\Model;

use Magento\Framework\Validation\ValidationResult;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\ReCaptchaValidationApi\Api\Data\ValidationConfigInterface;

class RecaptchaValidator
{
    /**
     *
     * @param \Magento\Framework\Validation\ValidationResultFactory $validationResultFactory
     */
    public function __construct(
        ValidationResultFactory $validationResultFactory
    ) {
        $this->validationResultFactory = $validationResultFactory;
    }

    /**
     * Undocumented function
     *
     * @param [type] $subject
     * @param [type] $proceed
     * @param string $reCaptchaResponse
     * @param \Magento\ReCaptchaValidationApi\Api\Data\ValidationConfigInterface $validationConfig
     *
     * @return \Magento\Framework\Validation\ValidationResult
     */
    public function aroundIsValid($subject, $proceed, string $reCaptchaResponse, ValidationConfigInterface $validationConfig):ValidationResult
    {
        $proceed($reCaptchaResponse, $validationConfig);
        return $this->validationResultFactory->create(['errors' => []]);
    }
}
