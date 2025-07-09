<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplaceEventManager
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MarketplaceEventManager\Plugin\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ProductOptions\ConfigInterface;
use Magento\Catalog\Model\Config\Source\Product\Options\Price as ProductOptionsPrice;
use Magento\Framework\UrlInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\Component\Form\Element\Hidden;
use Magento\Ui\Component\Modal;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Element\Checkbox;
use Magento\Ui\Component\Form\Element\ActionDelete;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Framework\Locale\CurrencyInterface;

class CustomOptions extends \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions
{
    public const FIELD_QTY = "qty";

    /**
     * @var \Magento\Catalog\Model\Locator\LocatorInterface
     */
    protected $locator;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\ProductOptions\ConfigInterface
     */
    protected $productOptionsConfig;

    /**
     * @var \Magento\Catalog\Model\Config\Source\Product\Options\Price
     */
    protected $productOptionsPrice;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;
    
    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @var CurrencyInterface
     */
    private $localeCurrency;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @param LocatorInterface $locator
     * @param StoreManagerInterface $storeManager
     * @param ConfigInterface $productOptionsConfig
     * @param ProductOptionsPrice $productOptionsPrice
     * @param UrlInterface $urlBuilder
     * @param ArrayManager $arrayManager
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        LocatorInterface $locator,
        StoreManagerInterface $storeManager,
        ConfigInterface $productOptionsConfig,
        ProductOptionsPrice $productOptionsPrice,
        UrlInterface $urlBuilder,
        ArrayManager $arrayManager,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->locator = $locator;
        $this->storeManager = $storeManager;
        $this->productOptionsConfig = $productOptionsConfig;
        $this->productOptionsPrice = $productOptionsPrice;
        $this->urlBuilder = $urlBuilder;
        $this->arrayManager = $arrayManager;
        $this->_request = $request;
    }

    /**
     * AfterModifyData
     *
     * @param \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions $subject
     * @param array $data
     * @param array $result
     */
    public function afterModifyData(
        \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions $subject,
        $data,
        $result
    ) {
        if ($this->_request->getParam('type') == 'etickets'
        || $this->locator->getProduct()->getTypeId() == 'etickets') {
        
            $options = [];
            $productOptions = $this->locator->getProduct()->getOptions() ?: [];

            /** @var \Magento\Catalog\Model\Product\Option $option */
            foreach ($productOptions as $index => $option) {
                $customOptionData = $option->getData();
                $customOptionData[static::FIELD_IS_USE_DEFAULT] = !$option->getData(static::FIELD_STORE_TITLE_NAME);
                $options[$index] = $subject->formatPriceByPath(static::FIELD_PRICE_NAME, $customOptionData);
                $optionValues = $option->getValues() ?: [];

                foreach ($optionValues as $optionValue) {
                    $optionValue->setData(
                        static::FIELD_IS_USE_DEFAULT,
                        !$optionValue->getData(static::FIELD_STORE_TITLE_NAME)
                    );
                }
                /** @var \Magento\Catalog\Model\Product\Option $optionValue */
                foreach ($optionValues as $optionValue) {
                    $options[$index][static::GRID_TYPE_SELECT_NAME][] = $subject->formatPriceByPath(
                        static::FIELD_PRICE_NAME,
                        $optionValue->getData()
                    );
                }
            }
            if (empty($options)) {
                $options[0] = ['title'=> 'Ticket Types', 'type'=>'radio'];
            }

            return array_replace_recursive(
                $data,
                [
                    $this->locator->getProduct()->getId() => [
                        static::DATA_SOURCE_DEFAULT => [
                            static::FIELD_ENABLE => 1,
                            static::GRID_OPTIONS_NAME => $options,
                            'event_start_date_tmp' => $this->locator->getProduct()->getEventStartDate(),
                            'event_end_date_tmp' => $this->locator->getProduct()->getEventEndDate()
                        ]
                    ]
                ]
            );
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function afterModifyMeta(
        \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions $subject,
        $meta,
        $result
    ) {
        if ($this->_request->getParam('type') == 'etickets' ||
            $this->locator->getProduct()->getTypeId() == 'etickets') {
            $meta = array_replace_recursive(
                $meta,
                [
                    static::GROUP_CUSTOM_OPTIONS_NAME => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'label' => __('Customizable Options'),
                                    'componentType' => Fieldset::NAME,
                                    'dataScope' => static::GROUP_CUSTOM_OPTIONS_SCOPE,
                                    'collapsible' => false,
                                    'additionalClasses' => 'wk-etickets-custom-option',
                                    'sortOrder' => $this->getNextGroupSortOrder(
                                        $meta,
                                        static::GROUP_CUSTOM_OPTIONS_PREVIOUS_NAME,
                                        static::GROUP_CUSTOM_OPTIONS_DEFAULT_SORT_ORDER
                                    ),
                                ],
                            ],
                        ],
                        'children' => [
                            static::FIELD_ENABLE => $this->getEnableFieldConfig(20),
                            static::GRID_OPTIONS_NAME => $this->getOptionsGridConfig(30)
                        ]
                    ]
                ]
            );

            $temp['ticket-booking'] = [
                'children' => [
                    'event_start_date_tmp' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'formElement' => 'date',
                                    'componentType' => 'field',
                                    'visible' => 1,
                                    'required' => 1,
                                    'options' => [
                                        'dateFormat' => 'MM/dd/yyyy',
                                        'minDate' => 'today',
                                        'timeFormat' => 'HH:mm:ss',
                                        'showsTime' => true,
                                    ],
                                    'dataScope' => 'event_start_date_tmp',
                                    'dataType' => 'string',
                                    'sortOrder' => 25,
                                    'validation' => [
                                        'required-entry' => true
                                    ],
                                    'label' => __('Event Start Time')
                                ]
                            ]
                        ]
                    ],
                    'event_end_date_tmp' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'formElement' => 'date',
                                    'componentType' => 'field',
                                    'visible' => 1,
                                    'required' => 1,
                                    'options' => [
                                        'dateFormat' => 'MM/dd/yyyy',
                                        'minDate' => 'today',
                                        'timeFormat' => 'HH:mm:ss',
                                        'showsTime' => true,
                                    ],
                                    'dataScope' => 'event_end_date_tmp',
                                    'dataType' => 'string',
                                    'sortOrder' => 26,
                                    'validation' => [
                                        'required-entry' => true
                                    ],
                                    'label' => __('Event End Time')
                                ]
                            ]
                        ]
                    ]
                ]
            ];
            $meta = array_merge_recursive($meta, $temp);
            return $meta;
        }
        return $meta;
    }

    /**
     * Get config for the whole grid
     *
     * @param int $sortOrder
     * @return array
     * @since 101.0.0
     */
    protected function getOptionsGridConfig($sortOrder)
    {
        if ($this->_request->getParam('type') == 'etickets' ||
        $this->locator->getProduct()->getTypeId() == 'etickets') {
            return [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'addButtonLabel' => __('Add Option'),
                            'componentType' => DynamicRows::NAME,
                            'component' => 'Magento_Catalog/js/components/dynamic-rows-import-custom-options',
                            'template' => 'ui/dynamic-rows/templates/collapsible',
                            'additionalClasses' => 'admin__field-wide',
                            'deleteProperty' => static::FIELD_IS_DELETE,
                            'deleteValue' => '1',
                            'addButton' => false,
                            'renderDefaultRecord' => false,
                            'columnsHeader' => false,
                            'collapsibleHeader' => true,
                            'sortOrder' => $sortOrder,
                            'dataProvider' => static::CUSTOM_OPTIONS_LISTING,
                            'links' => [
                                'insertData' => '${ $.provider }:${ $.dataProvider }',
                                '__disableTmpl' => ['insertData' => false],
                            ],
                        ],
                    ],
                ],
                // 'children' => [
                //     'record' => [
                //         'arguments' => [
                //             'data' => [
                //                 'config' => [
                //                     'headerLabel' => __('New Option'),
                //                     'component' => 'Magento_Ui/js/dynamic-rows/record',
                //                     'componentType' => Container::NAME,
                //                     'positionProvider' =>
                //                         static::CONTAINER_OPTION . '.' . static::FIELD_SORT_ORDER_NAME,
                //                     'isTemplate' => true,
                //                     'is_collection' => true,
                //                 ],
                //             ],
                //         ],
                //         'children' => [
                //             static::CONTAINER_OPTION => [
                //                 'arguments' => [
                //                     'data' => [
                //                         'config' => [
                //                             'componentType' => Fieldset::NAME,
                //                             'label' => null,
                //                             'collapsible' => true,
                //                             'sortOrder' => 10,
                //                             'opened' => true,
                //                         ],
                //                     ],
                //                 ],
                //                 'children' => [
                //                     static::CONTAINER_COMMON_NAME => $this->getCommonContainerConfig(10),
                //                     static::FIELD_SORT_ORDER_NAME => $this->getPositionFieldConfig(40),
                //                     static::GRID_TYPE_SELECT_NAME => $this->getSelectTypeGridConfig(30)
                //                 ]
                //             ],
                //         ]
                //     ]
                // ]
            ];
        }
    }

    /**
     * Get config for container with common fields for any type
     *
     * @param int $sortOrder
     * @return array
     * @since 101.0.0
     */
    protected function getCommonContainerConfig($sortOrder)
    {
        if ($this->_request->getParam('type') == 'etickets' ||
        $this->locator->getProduct()->getTypeId() == 'etickets') {
            $commonContainer = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'componentType' => Container::NAME,
                            'formElement' => Container::NAME,
                            'breakLine' => false,
                            'showLabel' => false,
                            'component' => 'Magento_Ui/js/form/components/group',
                            'additionalClasses' => 'admin__field-group-columns admin__control-group-equal',
                            'sortOrder' => $sortOrder,
                        ],
                    ],
                ],
                'children' => [
                    static::FIELD_OPTION_ID => $this->getOptionIdFieldConfig(10),
                    static::FIELD_TITLE_NAME => $this->getTitleFieldConfig(
                        20,
                        [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'label' => __('Option Title'),
                                        'valueUpdate' => 'input',
                                        'component' => 'Magento_Catalog/component/static-type-input',
                                        'imports' => [
                                            'isUseDefault' => '${ $.provider }:${ $.parentScope }.is_use_default',
                                            'optionId' => '${ $.provider }:${ $.parentScope }.option_id',
                                            '__disableTmpl' => ['optionId' => false, 'isUseDefault' => false],
                                        ],
                                        'value' => 'Ticket Types',
                                        'disabled' => "disabled",
                                        'readonly' => "readonly",
                                    ],
                                ],
                            ],
                        ]
                    ),
                    static::FIELD_IS_REQUIRE_NAME => $this->getIsRequireFieldConfigForEticket(40),
                    static::FIELD_TYPE_NAME => $this->getTypeFieldConfig(30)
                ]
            ];
        }

        if ($this->locator->getProduct()->getStoreId()) {
            $titlePath = $this->arrayManager->findPath(static::FIELD_TITLE_NAME, $commonContainer, null)
                . static::META_CONFIG_PATH;
            $useDefaultConfig = [
                'service' => [
                    'template' => 'Magento_Catalog/form/element/helper/custom-option-service',
                ]
            ];
            $commonContainer = $this->arrayManager->merge($titlePath, $commonContainer, $useDefaultConfig);
        }

        return $commonContainer;
    }

    /**
     * Get config for grid for "select" types
     *
     * @param int $sortOrder
     * @return array
     * @since 101.0.0
     */
    protected function getSelectTypeGridConfig($sortOrder)
    {
        if ($this->_request->getParam('type') == 'etickets' ||
        $this->locator->getProduct()->getTypeId() == 'etickets') {
            return [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'addButtonLabel' => __('Add Value'),
                            'component' => 'Magento_Ui/js/dynamic-rows/dynamic-rows',
                            'componentType' => DynamicRows::NAME,
                            'additionalClasses' => 'admin__field-wide',
                            'deleteProperty' => static::FIELD_IS_DELETE,
                            'deleteValue' => '1',
                            'renderDefaultRecord' => false,
                            'sortOrder' => $sortOrder,
                        ],
                    ],
                ],
                'children' => [
                    'record' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'component' => 'Magento_Ui/js/dynamic-rows/record',
                                    'componentType' => Container::NAME,
                                    'positionProvider' => static::FIELD_SORT_ORDER_NAME,
                                    'isTemplate' => true,
                                    'is_collection' => true,
                                ],
                            ],
                        ],
                        'children' => [
                            static::FIELD_TITLE_NAME => $this->getTitleFieldConfig(10),
                            static::FIELD_PRICE_NAME => $this->getPriceFieldConfig(20),
                            static::FIELD_PRICE_TYPE_NAME => $this->getPriceTypeFieldConfig(30, ['fit' => true]),
                            static::FIELD_SKU_NAME => $this->getSkuFieldConfig(40),
                            static::FIELD_QTY => $this->getQtyFieldConfig(50),
                            static::FIELD_SORT_ORDER_NAME => $this->getPositionFieldConfig(60),
                            static::FIELD_IS_DELETE => $this->getIsDeleteFieldConfig(70)
                        ]
                    ]
                ]
            ];
        }
    }

    /**
     * Get config for "Option Type" field
     *
     * @param int $sortOrder
     * @return array
     * @since 101.0.0
     */
    protected function getTypeFieldConfig($sortOrder)
    {
        if ($this->_request->getParam('type') == 'etickets' ||
        $this->locator->getProduct()->getTypeId() == 'etickets') {
            return [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('Option Type'),
                            'componentType' => Field::NAME,
                            'formElement' => Select::NAME,
                            'component' => 'Magento_Catalog/js/custom-options-type',
                            'elementTmpl' => 'ui/grid/filters/elements/ui-select',
                            'selectType' => 'optgroup',
                            'dataScope' => static::FIELD_TYPE_NAME,
                            'dataType' => Text::NAME,
                            'sortOrder' => $sortOrder,
                            'options' => [['value' => 'radio', 'label' => __('Radio Buttons')]],
                            'disableLabel' => true,
                            'multiple' => false,
                            'selectedPlaceholders' => [
                                'defaultPlaceholder' => __('-- Please select --'),
                            ],
                            'validation' => [
                                'required-entry' => true
                            ],
                            'groupsConfig' => [
                                'select' => [
                                    'values' => ['radio'],
                                    'indexes' => [
                                        static::GRID_TYPE_SELECT_NAME
                                    ]
                                ],
                            ],
                        ],
                    ],
                ],
            ];
        }
    }

    /**
     * Get config for "Required" field
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getIsRequireFieldConfigForEticket($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Required'),
                        'componentType' => Field::NAME,
                        'formElement' => Checkbox::NAME,
                        'dataScope' => static::FIELD_IS_REQUIRE_NAME,
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                        'value' => '1',
                        'valueMap' => [
                            'true' => '1',
                            'false' => '0'
                        ],
                        'disabled' => "disabled",
                        'readonly' => "readonly",
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for "SKU" field
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getQtyFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Qty'),
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'dataScope' => static::FIELD_QTY,
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for "Price" field for select type.
     *
     * @param int $sortOrder
     * @return array
     */
    private function getPriceFieldConfigForSelectType(int $sortOrder)
    {
        $priceFieldConfig = $this->getPriceFieldConfig($sortOrder);
        $priceFieldConfig['arguments']['data']['config']['template'] = 'Magento_Catalog/form/field';

        return $priceFieldConfig;
    }
}
