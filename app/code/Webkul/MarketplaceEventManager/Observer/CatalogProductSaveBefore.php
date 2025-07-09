<?php
/**
 * Webkul MarketplaceEventManager CatalogProductSaveBefore Observer.
 * @category  Webkul
 * @package   Webkul_MarketplaceEventManager
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MarketplaceEventManager\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class CatalogProductSaveBefore implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localeDate;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param TimezoneInterface $localeDate
     * @param ProductRepositoryInterface $productRepository
     * @param RequestInterface $request
     * @param ScopeConfigInterface $scopeInterface
     * @param \Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory $productCustomOption
     */
    public function __construct(
        TimezoneInterface $localeDate,
        ProductRepositoryInterface $productRepository,
        RequestInterface $request,
        ScopeConfigInterface $scopeInterface,
        \Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory $productCustomOption
    ) {
        $this->localeDate = $localeDate;
        $this->productRepository = $productRepository;
        $this->request = $request;
        $this->scopeConfig = $scopeInterface;
        $this->productCustomOption = $productCustomOption;
    }

    /**
     * Product save event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getProduct();
        $productData = $this->request->getParam('product');
        $type = $this->request->getParam('type');
        if ($type == 'etickets') {
            if (!empty($productData['event_start_date_tmp'])) {
                $product->setEventStartDate($productData['event_start_date_tmp']);
            }
            if (!empty($productData['event_end_date_tmp'])) {
                $product->setEventEndDate($productData['event_end_date_tmp']);
            }
            $product->setIsMpEvent(1);
            $i=0;
            $options = $this->mergeProductOptions(
                $productData['options'],
                []
            );
            $customOptions = [];
            foreach ($options as $items) {
                if (!isset($items['values'])) {
                    unset($productData['options'][$i]);
                    continue;
                }
                if (empty($items['is_delete'])) {
                    if (empty($items['option_id'])) {
                        $items['option_id'] = null;
                    }
                    if (isset($items['values'])) {
                        $items['values'] = array_filter(
                            $items['values'],
                            function ($valueData) {
                                return empty($valueData['is_delete']);
                            }
                        );
                    }
                    $customOption = $this->productCustomOption->create(['data' => $items]);
                    $customOption->setProductSku($product->getSku());
                    $customOptions[] = $customOption;
                }
                $i++;
            }
            $product->setOptions($customOptions);
        }
        return $this;
    }

    /**
     * Merge product and default options for product.
     *
     * @param array $productOptions   product options
     * @param array $overwriteOptions default value options
     *
     * @return array
     */
    public function mergeProductOptions($productOptions, $overwriteOptions)
    {
        if (!is_array($productOptions)) {
            return [];
        }

        if (!is_array($overwriteOptions)) {
            return $productOptions;
        }

        foreach ($productOptions as $index => $option) {
            $optionId = $option['option_id'];

            if (!isset($overwriteOptions[$optionId])) {
                continue;
            }

            foreach ($overwriteOptions[$optionId] as $fieldName => $overwrite) {
                if ($overwrite && isset($option[$fieldName]) && isset($option['default_'.$fieldName])) {
                    $productOptions[$index][$fieldName] = $option['default_'.$fieldName];
                }
            }
        }

        return $productOptions;
    }
}
