<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_TwoFactorAuth
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\TwoFactorAuth\Block;

use Magento\Framework\View\Element\Template\Context;

class PushHome extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Webkul\TwoFactorAuth\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $helperFactory;

    /**
     * @param Context $context
     * @param \Webkul\TwoFactorAuth\Helper\Data $helper
     * @param \Magento\Framework\ObjectManagerInterface $helperFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Webkul\TwoFactorAuth\Helper\Data $helper,
        \Magento\Framework\ObjectManagerInterface $helperFactory,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->helperFactory = $helperFactory;
        parent::__construct($context, $data);
    }

    /**
     * Get secure url
     *
     * @return string
     */
    public function getSecureUrl()
    {
        return $this->helper->getSecureUrl();
    }

    /**
     * Get sender id
     *
     * @return string
     */
    public function getSenderId()
    {
        return $this->helper->getSenderId();
    }

     /**
      * Get sender key
      *
      * @return string
      */
    public function getServerKey()
    {
        return $this->helper->getServerKey();
    }
    
    /**
     * Helper
     *
     * @param String $className
     * @return object
     */
    public function helper($className)
    {
        $helper = $this->helperFactory->get($className);
        if (false === $helper instanceof \Magento\Framework\App\Helper\AbstractHelper) {
            throw new \LogicException($className . ' doesn\'t extends Magento\Framework\App\Helper\AbstractHelper');
        }
        return $helper;
    }
}
