<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Marketplace\Plugin\Customer\Controller\Adminhtml\Index;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Controller\Adminhtml\Index\Save as CustomerSaveController;

class Save
{
    /**
     * After execute
     *
     * @param CustomerSaveController $subject
     * @param object $result
     * @return $result
     */
    public function afterExecute(CustomerSaveController $subject, $result)
    {
        
        $customerId = $this->getCurrentCustomerId($subject);
        $sellerPanel = trim($subject->getRequest()->getParam("seller_panel"));

        if( $subject->getRequest()->isPost() ){
            $fields = $this->getSellerProfileFields($subject->getRequest()->getParams());
            $this->saveSellerProfile($customerId,$fields);
        }

        if ($sellerPanel) {
            $path = $result->getPath();
            if (strpos($path, "customer/index") !== false) {
                return $result->setPath("marketplace/seller");
            } else {
                if ($customerId) {
                    $result->setPath(
                        'customer/*/edit',
                        ['id' => $customerId, 'seller_panel' => 1, '_current' => true]
                    );
                }
            }
        }

        return $result;
    }

    private function saveSellerProfile($sellerId,$fields){
        if( empty($fields) )
            return ;
        $taxvat = @$fields['taxvat']; unset($fields['taxvat']);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $sellerModel = $objectManager->get('Webkul\Marketplace\Model\SellerFactory');

        $autoId = 0;
        $collection = $sellerModel->create()->getCollection()->addFieldToFilter('seller_id', $sellerId);
        foreach ($collection as $value) {
            $autoId = $value->getId();
        }

        $value = $sellerModel->create()->load($autoId);
        if( isset($fields['store_id']) ){
            $value->setStoreId($fields['store_id']);
            unset($fields['store_id']);
        }

        $value->addData($fields);
        if (!$autoId) 
            $value->setCreatedAt(date("Y-m-d H:i:s"));
        
        $value->setUpdatedAt(date("Y-m-d H:i:s"));
        
        if ($fields['company_description']) {
            $fields['company_description'] = str_replace(
                'script',
                '',
                $fields['company_description']
            );
        }
        $value->setCompanyDescription($fields['company_description']);

        if (isset($fields['return_policy'])) {
            $fields['return_policy'] = str_replace(
                'script',
                '',
                $fields['return_policy']
            );
            $value->setReturnPolicy($fields['return_policy']);
        }

        if (isset($fields['shipping_policy'])) {
            $fields['shipping_policy'] = str_replace(
                'script',
                '',
                $fields['shipping_policy']
            );
            $value->setShippingPolicy($fields['shipping_policy']);
        }

        if (isset($fields['privacy_policy'])) {
            $fields['privacy_policy'] = str_replace(
                'script',
                '',
                $fields['privacy_policy']
            );
            $value->setPrivacyPolicy($fields['privacy_policy']);
        }

        $value->setMetaDescription($fields['meta_description']);

        /**
         * Set taxvat number for seller
         */
        if ($taxvat){
            $customer = $objectManager->get('Magento\Customer\Model\CustomerFactory')->create()->load($sellerId);
            $customer->setTaxvat($taxvat);
            $customer->setId($sellerId)->save();
        }

        $fileUploaderFactory =$objectManager->get('Magento\MediaStorage\Model\File\UploaderFactory');
        $mediaDirectory = $objectManager->get('Magento\Framework\Filesystem')->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $target = $mediaDirectory->getAbsolutePath('avatar/');
        try {
            
            $uploader = $fileUploaderFactory->create(
                ['fileId' => 'banner_pic']
            );
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            $uploader->setAllowRenameFiles(true);
            $result = $uploader->save($target);
            if ($result['file']) {
                $value->setBannerPic($result['file']);
            }
        } catch (\Exception $e) {
        
        }
        try {
            $uploaderLogo = $fileUploaderFactory->create(
                ['fileId' => 'logo_pic']
            );
            $uploaderLogo->setAllowedExtensions(['jpg', 'jpeg', 'png']);
            $uploaderLogo->setAllowRenameFiles(true);
            $resultLogo = $uploaderLogo->save($target);
            if ($resultLogo['file']) {
                $value->setLogoPic($resultLogo['file']);
            }
        } catch (\Exception $e) {
        }

        if (array_key_exists('country_pic', $fields)) {
            $value->setCountryPic($fields['country_pic']);
        }
        
        if (array_key_exists('country_pic', $fields)) {
            $value->setCountryPic($fields['country_pic']);
        }
    
        $value->save();
    
    }

    protected function getSellerProfileFields($_fields = [])
    {
        $fields = [];
        foreach (['featured', 'store_id', 'twitter_id', 'facebook_id', 'gplus_id', 'youtube_id', 'vimeo_id', 'instagram_id', 'pinterest_id', 'moleskine_id', 'tw_active', 'fb_active', 'gplus_active', 'youtube_active', 'vimeo_active', 'instagram_active', 'pinterest_active', 'moleskine_active', 'contact_number', 'taxvat', 'shop_title', 'company_locality', 'company_description', 'return_policy', 'shipping_policy', 'privacy_policy', 'meta_keyword', 'meta_description'] as $key) {
            $key = trim($key);
            if( isset($_fields[$key]) )
                $fields[$key] = $_fields[$key];
        }
        return $fields;
    }

    /**
     * Retrieve current customer ID
     *
     * @param mixed $subject
     * @return int
     */
    protected function getCurrentCustomerId($subject)
    {
        $originalRequestData = $subject->getRequest()->getPostValue(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER);

        $customerId = isset($originalRequestData['entity_id'])
            ? $originalRequestData['entity_id']
            : null;

        return $customerId;
    }
}
