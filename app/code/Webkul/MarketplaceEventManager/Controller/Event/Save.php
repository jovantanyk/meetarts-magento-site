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
namespace Webkul\MarketplaceEventManager\Controller\Event;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;

/**
 * Webkul MarketplaceEventManager Event Save Controller.
 */
class Save extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @var SaveProduct
     */
    protected $_saveProduct;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $_productResourceModel;

    /**
     * @var \Webkul\MarketplaceEventManager\Model\Product
     */
    protected $marketplaceEventManagerProduct;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param FormKeyValidator $formKeyValidator
     * @param \Webkul\Marketplace\Controller\Product\SaveProduct $saveProduct
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResourceModel
     * @param \Magento\Catalog\Api\CategoryLinkManagementInterface $categoryLinkManagement
     * @param \Webkul\Marketplace\Helper\Data $marketplaceHelper
     * @param \Webkul\MarketplaceEventManager\Helper\Data $helper
     * @param \Webkul\MarketplaceEventManager\Model\Product $marketplaceEventManagerProduct
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        FormKeyValidator $formKeyValidator,
        \Webkul\Marketplace\Controller\Product\SaveProduct $saveProduct,
        \Magento\Catalog\Model\ResourceModel\Product $productResourceModel,
        \Magento\Catalog\Api\CategoryLinkManagementInterface $categoryLinkManagement,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Webkul\MarketplaceEventManager\Helper\Data $helper,
        \Webkul\MarketplaceEventManager\Model\Product $marketplaceEventManagerProduct,
        \Magento\Framework\App\Response\RedirectInterface $redirect
    ) {
        $this->_customerSession = $customerSession;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_saveProduct = $saveProduct;
        $this->_productResourceModel = $productResourceModel;
        $this->helper = $helper;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->categoryLinkManagement = $categoryLinkManagement;
        $this->marketplaceEventManagerProduct = $marketplaceEventManagerProduct;
        $this->redirect = $redirect;
        parent::__construct(
            $context
        );
    }

    /**
     * Seller event ticket save action.
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        $helper = $this->marketplaceHelper;
        $isPartner = $helper->isSeller();
        if ($isPartner == 1) {
            try {
                $returnArr = [];
                $productId = '';
                if ($this->getRequest()->isPost()) {
                    if (!$this->_formKeyValidator->validate($this->getRequest())) {
                        return $this->resultRedirectFactory->create()->setPath(
                            '*/*/add',
                            ['_secure' => $this->getRequest()->isSecure()]
                        );
                    }

                    $wholedata = $this->getRequest()->getParams();
                    $skuType = $helper->getSkuType();
                    $skuPrefix = $helper->getSkuPrefix();
                    if ($skuType == 'dynamic') {
                        $sku = $skuPrefix.$wholedata['product']['name'];
                        $wholedata['product']['sku'] = $this->checkSkuExist($sku);
                    }

                    list($datacol, $errors) = $this->validatePost();

                    if (empty($errors)) {
                        // Qty save calculations for tickets
                        $tempqty = 0;
                        if (isset($wholedata['product']["options"])) {
                            list($tmpType, $tempqty) = $this->setQty($wholedata, $tempqty);
                        }
                        $wholedata['product']['quantity_and_stock_status'] = [
                            'qty'=>$tempqty,
                            'is_in_stock'=>($tempqty?1:0)
                        ];
                        $wholedata['product']['option_type'] = $tmpType;
                        $wholedata = $this->getCurrentTimeZoneEventDateTime($wholedata);
                        $returnArr = $this->_saveProduct->saveProductData(
                            $this->helper->getLoggedInSellerId(),
                            $wholedata
                        );
                        $productId = $returnArr['product_id'];

                        // save options to product
                        $data = ['has_options' => 1, 'required_options' => 1];
                        $productentity = $this->marketplaceEventManagerProduct->load($productId);
                        $productentity->addData($data)->setId($productId)->save();
                    } else {
                        foreach ($errors as $message) {
                            $this->messageManager->addError($message);
                        }
                    }
                }
                if ($productId != '') {
                    // clear cache
                    $this->marketplaceHelper->clearCache();
                    if (empty($errors)) {
                        $this->messageManager->addSuccess(
                            __('Your event has been successfully saved')
                        );
                    }

                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/edit',
                        [
                            'id' => $productId,
                            '_secure' => $this->getRequest()->isSecure(),
                        ]
                    );
                } else {
                    if (isset($returnArr['error']) && isset($returnArr['message'])) {
                        if ($returnArr['error'] && $returnArr['message'] != '') {
                            $this->messageManager->addError($returnArr['message']);
                        }
                    }

                    return $this->resultRedirectFactory->create()->setPath(
                        $this->redirect->getRefererUrl(),
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());

                return $this->resultRedirectFactory->create()->setPath(
                    $this->redirect->getRefererUrl(),
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());

                return $this->resultRedirectFactory->create()->setPath(
                    $this->redirect->getRefererUrl(),
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }

    /**
     * Set Qty
     *
     * @param array $wholedata
     * @param int $tempqty
     */
    public function setQty($wholedata, $tempqty)
    {
        foreach ($wholedata['product']["options"] as $tempoption) {
            $tmpType = $tempoption['type'];
            if (isset($tempoption['values'])) {
                foreach ($tempoption['values'] as $tempvalue) {
                    if (isset($tempvalue['qty']) && $tempvalue['qty']) {
                        $tempqty += $tempvalue['qty'];
                    }
                }
            }
        }
        return [$tmpType, $tempqty];
    }

    /**
     * GetCurrentTimeZoneEventDateTime
     *
     * @param array $wholedata
     */
    protected function getCurrentTimeZoneEventDateTime($wholedata)
    {
        $wholedata['product']['event_start_date'] = $this->helper->getDefaultZoneDateTime(
            $wholedata['product']['event_start_date']
        );
        $wholedata['product']['event_end_date'] = $this->helper->getDefaultZoneDateTime(
            $wholedata['product']['event_end_date']
        );
        $wholedata['product']['event_start_date_tmp'] = $wholedata['product']['event_start_date'];
        $wholedata['product']['event_end_date_tmp'] = $wholedata['product']['event_end_date'];

        $this->getRequest()->setParam('product', $wholedata['product']);
        return $wholedata;
    }

    /**
     * CheckSkuExist
     *
     * @param string $sku
     */
    private function checkSkuExist($sku)
    {
        try {
            $id = $this->_productResourceModel->getIdBySku($sku);
            if ($id) {
                $avialability = 0;
            } else {
                $avialability = 1;
            }
        } catch (\Exception $e) {
            $avialability = 0;
        }
        if ($avialability == 0) {
            $sku = $sku.rand();
            $sku = $this->checkSkuExist($sku);
        }
        return $sku;
    }

    /**
     * ValidatePost
     */
    private function validatePost()
    {
        $errors = [];
        $data = [];
        foreach ($this->getRequest()->getParam('product') as $code => $value) {
            switch ($code):
                case 'name':
                    $result = $this->nameValidateFunction($value, $code, $errors, $data);
                    $errors = $result['error'];
                    $data = $result['data'];
                    break;
                case 'description':
                    $result = $this->descriptionValidateFunction($value, $code, $errors, $data);
                    $errors = $result['error'];
                    $data = $result['data'];
                    break;
                case 'price':
                    $result = $this->priceValidateFunction($value, $code, $errors, $data);
                    $errors = $result['error'];
                    $data = $result['data'];
                    break;
                case 'weight':
                    $result = $this->weightValidateFunction($value, $code, $errors, $data);
                    $errors = $result['error'];
                    $data = $result['data'];
                    break;
                case 'stock':
                    $result = $this->stockValidateFunction($value, $code, $errors, $data);
                    $errors = $result['error'];
                    $data = $result['data'];
                    break;
                case 'sku_type':
                    $result = $this->skuTypeValidateFunction($value, $code, $errors, $data);
                    $errors = $result['error'];
                    $data = $result['data'];
                    break;
                case 'price_type':
                    $result = $this->priceTypeValidateFunction($value, $code, $errors, $data);
                    $errors = $result['error'];
                    $data = $result['data'];
                    break;
                case 'weight_type':
                    $result = $this->weightTypeValidateFunction($value, $code, $errors, $data);
                    $errors = $result['error'];
                    $data = $result['data'];
                    break;
                case 'bundle_options':
                    $result = $this->bundleOptionValidateFunction($value, $code, $errors, $data);
                    $errors = $result['error'];
                    $data = $result['data'];
            endswitch;
        }

        return [$data, $errors];
    }

    /**
     * NameValidateFunction
     *
     * @param string $value
     * @param string $code
     * @param string $errors
     * @param array $data
     */
    private function nameValidateFunction($value, $code, $errors, $data)
    {
        if (trim($value) == '') {
            $errors[] = __('Name has to be completed');
        } else {
            $data[$code] = $value;
        }
        return ['error' => $errors, 'data' => $data];
    }

    /**
     * DescriptionValidateFunction
     *
     * @param string $value
     * @param string $code
     * @param string $errors
     * @param array $data
     */
    private function descriptionValidateFunction($value, $code, $errors, $data)
    {
        if (trim($value) == '') {
            $errors[] = __(
                'Description has to be completed'
            );
        } else {
            $data[$code] = $value;
        }
        return ['error' => $errors, 'data' => $data];
    }

    /**
     * PriceValidateFunction
     *
     * @param string $value
     * @param string $code
     * @param string $errors
     * @param array $data
     */
    private function priceValidateFunction($value, $code, $errors, $data)
    {
        if (!preg_match('/^([0-9])+?[0-9.]*$/', $value)) {
            $errors[] = __(
                'Price should contain only decimal numbers'
            );
        } else {
            $data[$code] = $value;
        }
        return ['error' => $errors, 'data' => $data];
    }

    /**
     * WeightValidateFunction
     *
     * @param string $value
     * @param string $code
     * @param string $errors
     * @param array $data
     */
    private function weightValidateFunction($value, $code, $errors, $data)
    {
        if (!preg_match('/^([0-9])+?[0-9.]*$/', $value)) {
            $errors[] = __(
                'Weight should contain only decimal numbers'
            );
        } else {
            $data[$code] = $value;
        }
        return ['error' => $errors, 'data' => $data];
    }

    /**
     * StockValidateFunction
     *
     * @param string $value
     * @param string $code
     * @param string $errors
     * @param array $data
     */
    private function stockValidateFunction($value, $code, $errors, $data)
    {
        if (!preg_match('/^([0-9])+?[0-9.]*$/', $value)) {
            $errors[] = __(
                'Product stock should contain only integers'
            );
        } else {
            $data[$code] = $value;
        }
        return ['error' => $errors, 'data' => $data];
    }

    /**
     * SkuTypeValidateFunction
     *
     * @param string $value
     * @param string $code
     * @param string $errors
     * @param array $data
     */
    private function skuTypeValidateFunction($value, $code, $errors, $data)
    {
        if (trim($value) == '') {
            $errors[] = __('Sku Type has to be selected');
        } else {
            $data[$code] = $value;
        }
        return ['error' => $errors, 'data' => $data];
    }

    /**
     * PriceTypeValidateFunction
     *
     * @param string $value
     * @param string $code
     * @param string $errors
     * @param array $data
     */
    private function priceTypeValidateFunction($value, $code, $errors, $data)
    {
        if (trim($value) == '') {
            $errors[] = __('Price Type has to be selected');
        } else {
            $data[$code] = $value;
        }
        return ['error' => $errors, 'data' => $data];
    }

    /**
     * WeightTypeValidateFunction
     *
     * @param string $value
     * @param string $code
     * @param string $errors
     * @param array $data
     */
    private function weightTypeValidateFunction($value, $code, $errors, $data)
    {
        if (trim($value) == '') {
            $errors[] = __('Weight Type has to be selected');
        } else {
            $data[$code] = $value;
        }
        return ['error' => $errors, 'data' => $data];
    }

    /**
     * BundleOptionValidateFunction
     *
     * @param string $value
     * @param string $code
     * @param string $errors
     * @param array $data
     */
    private function bundleOptionValidateFunction($value, $code, $errors, $data)
    {
        if (trim($value) == '') {
            $errors[] = __('Default Title has to be completed');
        } else {
            $data[$code] = $value;
        }
        return ['error' => $errors, 'data' => $data];
    }
}
