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
namespace Webkul\MarketplaceEventManager\Controller\Rewrite;

use Magento\Eav\Model\Entity\Attribute\Exception;
use Magento\Framework\Exception\LocalizedException;

class ValidateProduct extends \Magento\Catalog\Controller\Adminhtml\Product\Validate
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Validate product
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $responseData = new \Magento\Framework\DataObject();
        $responseData->setError(false);

        try {
            $productData = $this->getRequest()->getPost('product', []);

            if ($productData && !isset($productData['stock_data']['use_config_manage_stock'])) {
                $productData['stock_data']['use_config_manage_stock'] = 0;
            }

            if (isset($productData['event_start_date_tmp'])
                && isset($productData['event_end_date_tmp'])
                && $productData['event_start_date_tmp']
                && $productData['event_end_date_tmp']) {
                $eventStartDate = $productData['event_start_date_tmp'];
                $eventEndDate = $productData['event_end_date_tmp'];
                if (strtotime($eventStartDate)<0 || strtotime($eventEndDate)<0) {
                    throw new \Magento\Framework\Exception\LocalizedException(__("Event Dates must be valid."));
                }
                if (strtotime($eventStartDate) >= strtotime($eventEndDate)) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __("You can not select previous date from Event Start Date as Event End Date.")
                    );
                }
            }
            $storeId = $this->getRequest()->getParam('store', 0);
            $store = $this->getStoreManager()->getStore($storeId);
            $this->getStoreManager()->setCurrentStore($store->getCode());
            /* @var $catalogProduct \Magento\Catalog\Model\Product */
            $catalogProduct = $this->productFactory->create();
            $catalogProduct->setData('_edit_mode', true);
            if ($storeId) {
                $catalogProduct->setStoreId($storeId);
            }
            $setId = $this->getRequest()->getPost('set') ?: $this->getRequest()->getParam('set');
            if ($setId) {
                $catalogProduct->setAttributeSetId($setId);
            }
            $typeId = $this->getRequest()->getParam('type');
            if ($typeId) {
                $catalogProduct->setTypeId($typeId);
            }
            $productId = $this->getRequest()->getParam('id');
            if ($productId) {
                $catalogProduct->load($productId);
            }
            $catalogProduct = $this->getInitializationHelper()->initializeFromData($catalogProduct, $productData);

            /* set restrictions for date ranges */
            $resource = $catalogProduct->getResource();
            $resource->getAttribute('special_from_date')->setMaxValue($catalogProduct->getSpecialToDate());
            $resource->getAttribute('news_from_date')->setMaxValue($catalogProduct->getNewsToDate());
            $resource->getAttribute('custom_design_from')->setMaxValue($catalogProduct->getCustomDesignTo());

            $this->productValidator->validate($catalogProduct, $this->getRequest(), $responseData);
        } catch (Exception $e) {
            $responseData->setError(true);
            $responseData->setAttribute($e->getAttributeCode());
            $responseData->setMessages([$e->getMessage()]);
        } catch (LocalizedException $e) {
            $responseData->setError(true);
            $responseData->setMessages([$e->getMessage()]);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $layout = $this->layoutFactory->create();
            $layout->initMessages();
            $responseData->setError(true);
            $responseData->setHtmlMessage($layout->getMessagesBlock()->getGroupedHtml());
        }

        return $this->resultJsonFactory->create()->setData($responseData);
    }

    /**
     * GetStoreManager
     *
     * @return StoreManagerInterface
     * @deprecated 101.0.0
     */
    private function getStoreManager()
    {
        if (null === $this->storeManager) {
            $this->storeManager = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Store\Model\StoreManagerInterface::class);
        }
        return $this->storeManager;
    }
}
