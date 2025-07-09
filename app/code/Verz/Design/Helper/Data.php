<?php

/**

 * @package   Verz_Design

 * @copyright Copyright (c) 2022 VerzDesign (https://www.verzdesign.com/)

 * @contacts  enquiry@verzdesign.com

 */



namespace Verz\Design\Helper;



class Data extends \Magento\Framework\App\Helper\AbstractHelper

{

	protected $_customerSession;



	public function __construct(

		\Magento\Framework\App\Helper\Context $context,

		\Magento\Customer\Model\Session $session,

		\Magento\Store\Model\StoreManagerInterface $storeManager,

		\Magento\Catalog\Model\CategoryRepository $categoryRepository,

		\Magento\Framework\Registry $registry,

		\Magento\Wishlist\Model\Wishlist $wishlist,

		\Magento\CatalogRule\Model\ResourceModel\Rule $productrule,

		\Magento\Framework\Stdlib\DateTime\DateTime $datetime,

		\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,

		\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,

		\Magento\Checkout\Model\Cart $cart,

		\Magento\Catalog\Model\ProductRepository $productRepository,

		\Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,

		\Magento\Framework\View\Page\Title $pageTitle,

		\Magento\Catalog\Model\Product\Visibility $productVisibility,

		\Magento\CatalogRule\Model\RuleFactory $ruleFactory,

		\Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface,

		\Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewFactory

	)

	{

		parent::__construct($context);

		$this->_customerSession = $session;

		$this->_storeManager = $storeManager;

		$this->categoryRepository = $categoryRepository;

		$this->registry = $registry;

		$this->wishlist = $wishlist;

		$this->productrule = $productrule;

		$this->datetime = $datetime;

		$this->_productCollectionFactory = $productCollectionFactory;

		$this->_categoryCollectionFactory = $categoryCollectionFactory;

		$this->_cart = $cart;

		$this->_productRepository = $productRepository;

		$this->productStatus = $productStatus;

		$this->productVisibility = $productVisibility;

		$this->_pageTitle = $pageTitle;

		$this->_reviewsColFactory = $reviewFactory;

		$this->ruleFactory = $ruleFactory;

		$this->timezoneInterface = $timezoneInterface;

	}



	/**

	 * Retrieve Page title

	 * @return string

	 */

	public function getPageTitle()

	{

		return $this->_pageTitle->getShort();

	}



	/**

	 * Retrieve Customer Login Detail

	 * @return array

	 */

	function isCustomerloggedIn()

	{

		if ($this->_customerSession->isLoggedIn()) {

			return $this->_customerSession;

		} else {

			return false;

		}

	}



	/**

	 * Retrieve Categoy Detail

	 * @param  int $categoryId

	 * @return array

	 */

	function getCategory($categoryId)

	{

		$category = $this->categoryRepository->get($categoryId, $this->_storeManager->getStore()->getId());

		return $category;

	}



	/**

	 * Retrieve Product Detail

	 * @param  int $id

	 * @return array

	 */

	public function getProductById($id)

	{

		return $this->_productRepository->getById($id);

	}



	/**

	 * Retrieve Current Category Detail

	 * @return array

	 */

	function getCurrentCategory()

	{

		$category = $this->registry->registry('current_category');

		return $category;

	}



	/**

	 * Retrieve Current Product Detail

	 * @return array

	 */

	function getCurrentProduct()

	{

		$product = $this->registry->registry('current_product');

		return $product;

	}



	/**

	 * Retrieve Customer Wishlist id

	 * @param  int $customer_id

	 * @return array

	 */

	function getWishlistId($customer_id)

	{

		$wishlist_collection = $this->wishlist->loadByCustomerId($customer_id, true)->getItemCollection();

		$wish_p_id = array();

		foreach ($wishlist_collection as $item) {

			$wish_p_id[] = $item->getProduct()->getId();

		}

		return $wish_p_id;

	}



	/**

	 * Retrieve Customer Count Wishlist

	 * @return int

	 */

	function getCountWishlistId()

	{

		if ($this->_customerSession->isLoggedIn()) {

			$customer_id = $this->_customerSession->getCustomer()->getId();

			$wishlist_collection = $this->wishlist->loadByCustomerId($customer_id, true)->getItemCollection();

			return count($wishlist_collection);

		} else {

			return 0;

		}

	}



	/**

	 * Retrieve Product Sale label

	 * @param  mixed $_product

	 * @return int

	 */

	function isSaleProduct($_product)

	{

		$orgprice = $_product->getPrice();

		$simplePrice = 0;

		$_savingPercent = 0;

		$simplePrice = $_product->getPrice();

		$rule = $this->ruleFactory->create();

		if ($_product->getTypeId() == "configurable") {

			$_children = $_product->getTypeInstance()->getUsedProducts($_product);

			foreach ($_children as $child) {

				$orgprice = $child->getPrice();

				$specialprice = $child->getSpecialPrice();

				$specialfromdate = $child->getSpecialFromDate();

				$specialtodate = $child->getSpecialToDate();

				$today = time();

				if ($specialprice) {

					if ((is_null($specialfromdate) && is_null($specialtodate)) || ($today >= strtotime($specialfromdate) && is_null($specialtodate)) || ($today <= strtotime($specialtodate) && is_null($specialfromdate)) || ($today >= strtotime($specialfromdate) && $today <= strtotime($specialtodate))) {

						$percanetage = (($orgprice - $specialprice) / $orgprice) * 100;

						return round($percanetage);

					}

				} else {

					$discountAmount = $rule->calcProductPriceRule($child, $child->getPrice());

					if ($discountAmount) {

						$orgprice = $child->getPrice();

						$specialprice = $child->getFinalPrice();

						$percanetage = (($orgprice - $specialprice) / $orgprice) * 100;

						//return round($percanetage);

					}

				}

			}

		} else {

			$specialprice = $_product->getSpecialPrice();

			$specialfromdate = $_product->getSpecialFromDate();

			$specialtodate = $_product->getSpecialToDate();

			$today = time();

			if (!$specialprice)

				$specialprice = $orgprice;

			if ($specialprice < $orgprice) {

				if ((is_null($specialfromdate) && is_null($specialtodate)) || ($today >= strtotime($specialfromdate) && is_null($specialtodate)) || ($today <= strtotime($specialtodate) && is_null($specialfromdate)) || ($today >= strtotime($specialfromdate) && $today <= strtotime($specialtodate))) {

					$percanetage = (($orgprice - $specialprice) / $orgprice) * 100;

					return round($percanetage);

				}

			} else {

				$discountAmount = $rule->calcProductPriceRule($_product, $_product->getPrice());

				if ($discountAmount) {

					$orgprice = $_product->getPrice();

					$specialprice = $_product->getFinalPrice();

					$percanetage = (($orgprice - $specialprice) / $orgprice) * 100;

					return round($percanetage);

				}

			}

		}

		return 0;

	}



	/**

	 * Retrieve Product New label

	 * @param  mixed $_product

	 * @return boolean

	 */

	function isNewProduct($_product)

	{

		$curdate = date('Y-m-d');

		if ($_product->getData('news_from_date') || $_product->getData('news_to_date')) {

			if ($_product->getData('news_to_date') && $curdate <= date('Y-m-d', strtotime($_product->getData('news_to_date')))) {

				return true;

			} elseif (!$_product->getData('news_to_date') && $curdate >= date('Y-m-d', strtotime($_product->getData('news_from_date')))) {

				return true;

			}

		}

		return false;

	}



	/**

	 * Retrieve Product Collection by Category

	 * @param  int $categories

	 * @param  int $limit

	 * @return array

	 */

	public function getProductCollectionByCategories($categories, $limit)

	{

		$category = $this->getCategory($categories);

		$_productCollection = $category->getProductCollection()->addAttributeToSelect('*')->addAttributeToSort('position', 'asc')->setPageSize($limit);

		return $_productCollection;

	}



	/**

	 * Retrieve Cart Item Count

	 * @return int

	 */

	public function getCartItem()

	{

		// get quote items collection

		$itemsCollection = $this->_cart->getQuote()->getItemsCollection();

		return count($itemsCollection);

	}



	/**

	 * Retrieve Media Url

	 * @return string

	 */

	public function getMediaUrl($path)

	{

		return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $path;

	}



	/**

	 * Retrieve Product Category

	 * @param  mixed $_product

	 * @return string

	 */

	public function getProductCategory($_product)

	{

		$categories = $_product->getCategoryIds();

		$pr_cat = "";

		if ($categories) {

			$definite_categories = array('2','9','10','5');

			$categories = array_diff($categories, $definite_categories);

			$categories = array_values($categories);

			$last_key = count($categories) - 1;

			if (count($categories) > 0)

				return $this->getProductCategorykeyValue($categories, $last_key);

			else

				return false;

		}

	}



	/**

	 * Retrieve Product Category name

	 * @param  array $categories

	 * @param  int $key

	 * @return string

	 */

	public function getProductCategorykeyValue($categories, $key)

	{

		if (isset($categories[$key])) {

			$pr_cat = $this->getCategory($categories[$key]);

			if ($pr_cat)

				return $pr_cat->getName();

		} else {

			$this->getProductCategorykeyValue($categories, $key - 1);

		}



	}



	/**

	 * Retrieve Reveiw List for Specific Product

	 * @param  mixed $_product

	 * @return array

	 */

	public function getReviewsCollection($_product)

	{

		$this->_reviewsCollection = $this->_reviewsColFactory->create()->addStoreFilter($this->_storeManager->getStore()->getId())->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)->addEntityFilter('product', $_product->getId())->setDateOrder();

		return $this->_reviewsCollection;

	}



	/**

	 * Retrieve Store config Value

	 * @param  string $config_path

	 * @return mixed

	 */

	public function getConfig($config_path)

	{

		return $this->scopeConfig->getValue(

			$config_path,

				\Magento\Store\Model\ScopeInterface::SCOPE_STORE

		);

	}



	/**

	 * Retrieve Store Date Time

	 * @return string

	 */

	public function getStoreDateTime()

	{

		$formatDate = $this->timezoneInterface->formatDate();

		// you can also get format wise date and time

		$dateTime = $this->timezoneInterface->date()->format('Y-m-d H:i:s');

		$date = $this->timezoneInterface->date()->format('Y-m-d');

		$time = $this->timezoneInterface->date()->format('H:i');

		return $dateTime;

	}

	/**

	 * Retrieve Categories

	 * @return array

	 */

	function getRootCategory()

	{

		$categoryCollection = $this->_categoryCollectionFactory->create();

		$categoryCollection->addAttributeToFilter('display_mode', array('neq' => "PAGE"));

		$categoryCollection->addAttributeToFilter('level', array('eq' => 2));

		$categoryCollection->setOrder('position', 'ASC');

		return $categoryCollection;

	}

	/**

	 * Get place holder image of a product for thumbnail

	 *

	 * @return string

	 */

	public function getPlaceHolderImage()

	{

		$imagePlaceholder = $this->_storeManager->getStore()->getConfig('catalog/placeholder/thumbnail_placeholder');

		if ($imagePlaceholder) {

			$mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

			return $mediaUrl . 'catalog/product/placeholder/' . $imagePlaceholder;

		}

		return "";

	}
	
    public function getSellerProductCollection($sellerId, $productId = 0, $productCount = 8)
    {
        $sellerId = (int) $sellerId;
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToFilter('type_id',array('neq' => 'etickets'));
        $collection->joinSellerProducts();

		$collection->getSelect()->where("mp_product.seller_id = $sellerId and e.entity_id != $productId");

        $collection->getSelect()->limit($productCount);
        return $collection;
    }
    public function getSellerEventsCollection($sellerId, $productId = 0, $productCount = 8)
    {
        $sellerId = (int) $sellerId;
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToFilter('type_id',array('eq' => 'etickets'));
        $collection->joinSellerProducts();

		$collection->getSelect()->where("mp_product.seller_id = $sellerId and e.entity_id != $productId");

        $collection->getSelect()->limit($productCount);
        return $collection;
    }

}