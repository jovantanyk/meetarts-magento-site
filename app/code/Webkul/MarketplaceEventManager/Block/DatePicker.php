<?php
namespace Webkul\MarketplaceEventManager\Block;
  
use Magento\Framework\Registry;
use Magento\Backend\Block\Template\Context;
  
class DatePicker extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var  Registry
     */
    protected $_coreRegistry;
  
    /**
     * Construct
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }
  
    /**
     * GetElementHtml
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        //get configuration element
        $html = $element->getElementHtml();
        //check datepicker set or not
        if (!$this->_coreRegistry->registry('datepicker_loaded')) {
            $this->_coreRegistry->registry('datepicker_loaded', 1);
        }
        //add icon on datepicker
        $html .= '<button type="button" style="display:none;" class="ui-datepicker-trigger '
            .'v-middle '.$element->getHtmlId().'"><span>Select Date</span></button>';
        // add datepicker with element by jquery
        $html .= '<script type="text/javascript">
            require(["jquery", "jquery/ui", "mage/calendar"], function (jq) {
                jq(document).ready(function () {
                    jq("#' .
                    $element->getHtmlId() . '")
                    .datetimepicker( { dateFormat: "yy-mm-dd", timeFormate: "HH:mm:ss" } );
                    jq(".ui-datepicker-trigger").removeAttr("style");
                    jq(".'.$element->getHtmlId().'").click(function(){
                        jq("#' . $element->getHtmlId() . '").focus();
                    });
                });
            });
            </script>';
        // return datepicker element
        return $html;
    }
}
