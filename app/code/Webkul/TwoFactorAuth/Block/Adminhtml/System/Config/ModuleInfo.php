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
namespace Webkul\TwoFactorAuth\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Context;
use Magento\Framework\Module\PackageInfoFactory;

class ModuleInfo extends \Magento\Config\Block\System\Config\Form\Field\Heading
{
    /**
     * @var PackageInfoFactory
     */
    protected $_packageInfoFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PackageInfoFactory $packageInfoFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        PackageInfoFactory $packageInfoFactory,
        array $data = []
    ) {
        $this->_packageInfoFactory = $packageInfoFactory;
        parent::__construct($context, $data);
    }

    /**
     * Render element html
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $label = $element->getLabel();

        $authorText = __('Author:');
        $authorTitle = __('Webkul Software Private Limited');
        $authorHref = 'https://webkul.com/';

        $guideText = __('User Guide:');
        $guideHref = 'https://webkul.com/blog/magento2-two-factor-authentication/';

        $extensionText = __('Store Extension:');
        $extensionHref = 'https://store.webkul.com/magento2-two-factor-authentication.html';

        $ticketText = __('Ticket/Customisations:');
        $ticketHref = 'https://webkul.uvdesk.com/en/customer/create-ticket/';

        $servicesText = __('Services:');
        $servicesHref = 'https://webkul.com/magento-development/';

        $webkulText = __('Webkul');
        $anchorText = __('Click here');

        $info = "<p>$authorText <a target='_blank' title='$authorTitle' href='$authorHref'>$webkulText</a></p>".
        "<p>$guideText <a target='_blank' href='$guideHref'>$anchorText</a></p>".
        "<p>$extensionText <a target='_blank' href='$extensionHref'>$anchorText</a></p>".
        "<p>$ticketText <a target='_blank' href='$ticketHref'>$anchorText</a></p>".
        "<p>$servicesText <a target='_blank' href='$servicesHref'>$anchorText</a></p>";
        $label .= $info;

        $packageInfo = $this->_packageInfoFactory->create();
        $version = $packageInfo->getVersion('Webkul_TwoFactorAuth');
        $label .= '<p>'.__('Version: %1', $version).'</p>';
        
        return sprintf(
            '<tr class="system-fieldset-sub-head" id="row_%s"><td colspan="5"><h4 id="%s">%s</h4></td></tr>',
            $element->getHtmlId(),
            $element->getHtmlId(),
            $label
        );
    }
}
