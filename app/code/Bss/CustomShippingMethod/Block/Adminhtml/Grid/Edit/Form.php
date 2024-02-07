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
namespace Bss\CustomShippingMethod\Block\Adminhtml\Grid\Edit;

use Magento\Framework\Data\Form\Element\Fieldset;

/**
 * Admin html Add New Form.
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * @var \Bss\CustomShippingMethod\Model\Status
     */
    protected $status;

    /**
     * @var \Magento\Directory\Model\Config\Source\Country
     */
    protected $country;

    /**
     * @var \Magento\Shipping\Model\Config\Source\Allspecificcountries
     */
    protected $allCountries;

    /**
     * @var \Bss\CustomShippingMethod\Model\CustomMethod
     */
    private $customMethod;

    /**
     * Form constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Directory\Model\Config\Source\Country $country
     * @param \Bss\CustomShippingMethod\Model\CustomMethod $customMethod
     * @param \Magento\Shipping\Model\Config\Source\Allspecificcountries $allSpecificountries
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Bss\CustomShippingMethod\Model\Status $status
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Directory\Model\Config\Source\Country $country,
        \Bss\CustomShippingMethod\Model\CustomMethod $customMethod,
        \Magento\Shipping\Model\Config\Source\Allspecificcountries $allSpecificountries,
        \Magento\Store\Model\System\Store $systemStore,
        \Bss\CustomShippingMethod\Model\Status $status,
        array $data = []
    ) {
        $this->status = $status;
        $this->country = $country;
        $this->customMethod = $customMethod;
        $this->allCountries= $allSpecificountries;
        $this->systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare Form
     *
     * @return \Magento\Backend\Block\Widget\Form\Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('row_data');
        $form = $this->_formFactory->create(
            ['data' => [
                'id' => 'edit_form',
                'enctype' => 'multipart/form-data',
                'action' => $this->getData('action'),
                'method' => 'post'
            ]
            ]
        );
        $form->setHtmlIdPrefix('bss_');
        if ($model->getEntityId()) {
            $fieldset = $form->addFieldset(
                'base_fieldset',
                ['legend' => __('Edit ' . $model->getName()), 'class' => 'fieldset-wide']
            );
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        } else {
            $fieldset = $form->addFieldset(
                'base_fieldset',
                ['legend' => __('Add Custom Method'), 'class' => 'fieldset-wide']
            );
            $fieldset->addField('id', 'hidden', ['values'=>""]);
        }
        $this->addFormBasicConfig($fieldset);
        $this->addFormSelectArea($fieldset);
        $this->addFormAmount($fieldset);
        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(\Magento\Framework\View\Element\Template::class)
                ->setTemplate('Bss_CustomShippingMethod::countriesjs.phtml')
        );
        if ($model->getData()) {
            $form->setValues($model->getData());
        }
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Add Form for basic config.
     *
     * @param Fieldset $fieldset
     */
    public function addFormBasicConfig($fieldset)
    {
        $fieldset->addField(
            'enabled',
            'select',
            [
                'name' => 'enabled',
                'label' => __('Enabled In'),
                'id' => 'enabled',
                'title' => __('Enabled'),
                'values' => $this->status->getOptionArray()
            ]
        );
        $fieldset->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => __('Name'),
                'id' => 'name',
                'required' => true,
                'title' => __('Name')
            ]
        );
        $fieldset->addField(
            'type',
            'select',
            [
                'name' => 'type',
                'label' => __('Type'),
                'id' => 'type',
                'title' => __('Type'),
                'values' => [
                    ["value" =>'',"label" => __("None")],
                    ["value" => "O","label" => __("Per Order")],
                    ["value"  => "I","label" => __("Per Item")]
                ],
                'value'=> "I"
            ]
        );
        $fieldset->addField(
            'price',
            'text',
            [
                'name' => 'price',
                'label' => __('Price'),
                'id' => 'price',
                'title' => __('Price'),
                'class' => "validate-number validate-not-negative-number"
            ]
        );
        $fieldset->addField(
            'calculate_handling_fee',
            'select',
            [
                'name' => 'calculate_handling_fee',
                'label' => __('Calculate Handling Fee'),
                'values' => [
                    ["value" => "F","label" => __('Fixed')],
                    ["value" => "P","label" => __('Percent')]
                ]
            ]
        );
        $fieldset->addField(
            'handling_fee',
            'text',
            [
                'name' => 'handling_fee',
                'label' => __('Handling Fee'),
                'id' => 'handling_fee',
                'title' => __('Handling Fee'),
                'class' => "validate-number validate-not-negative-number"
            ]
        );
    }

    /**
     * Add Form for amount config.
     *
     * @param Fieldset $fieldset
     */
    public function addFormAmount($fieldset)
    {
        $fieldset->addField(
            'minimum_order_amount',
            'text',
            [
                'name' => 'minimum order amount',
                'label' => __('Minimum order Amount'),
                'id' => 'minimum order amount',
                'class' => "validate-number validate-not-negative-number"
            ]
        );
        $fieldset->addField(
            'maximum_order_amount',
            'text',
            [
                'name' => 'maximum order amount',
                'label' => __('Maximum order Amount'),
                'id' => 'maximum order amount',
                'class' => "validate-number validate-not-negative-number"
            ]
        );
        $fieldset->addField(
            'sort_order',
            'text',
            [
                'name' => 'sort order',
                'label' => __('Sort Order'),
                'id' => 'sort order',
                'class' => "validate-number validate-not-negative-number"
            ]
        );
        $fieldset->addField(
            'store_id',
            'multiselect',
            [
                'name' => 'store_id',
                'label' => __('Store View'),
                'id' => 'store_id',
                'required' => true,
                'values' => $this->systemStore->getStoreValuesForForm(false, true)
            ]
        );
    }

    /**
     * Add Form for select Area section.
     *
     * @param Fieldset $fieldset
     */
    public function addFormSelectArea($fieldset)
    {
        $fieldset->addField(
            'applicable_countries',
            'select',
            [
                'name' => 'applicable_countries',
                'label' => __('Ship to'),
                'id' => 'applicable_countries',
                'values' => [
                    ['value' => 0, 'label' => __('All Allowed Countries')],
                    ['value' => 1, 'label' => __('Specific Countries')],
                    ['value' => 2, 'label' => __('Specific Areas')]
                ]
            ]
        );
        $fieldset->addField(
            'specific_countries',
            'multiselect',
            [
                'name' => 'specific_countries',
                'label' => __('Specific Countries'),
                'id' => 'specific_countries',
                'values' =>$this->country->toOptionArray(true, '')
            ]
        );

        $fieldset->addField(
            'specific_country',
            'select',
            [
                'name' => 'specific_country',
                'label' => __('Country'),
                'id' => 'specific_country',
                'values' =>$this->country->toOptionArray(true, '')
            ]
        );

        $fieldset->addField(
            'specific_regions',
            'multiselect',
            [
                'name' => 'specific_regions',
                'label' => __('Regions/States'),
                'id' => 'specific_regions',
            ]
        );
        $fieldset->addField(
            'specific_regions_',
            'text',
            [
                'name' => 'specific_regions_',
                'label' => __('Regions/States'),
                'id' => 'specific_regions_',
                'title' => __('state'),
                'class' => 'specific_regions_',
                'required' => false
            ]
        );
    }

    /**
     * Get area of shipping method
     * @return mixed
     */
    public function getSpecificArea()
    {
        $model = $this->_coreRegistry->registry('row_data');
        return [
            'country' => $model->getSpecificCountry(),
            'regions' => $model->getSpecificRegions()
        ];
    }
}
