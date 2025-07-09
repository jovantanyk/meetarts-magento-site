<?php
/**
 * @package   Verz_Design
 * @copyright Copyright (c) 2022 VerzDesign (https://www.verzdesign.com/)
 * @contacts  enquiry@verzdesign.com
 */

namespace Verz\MenuOverride\Block\Html;

class Topmenu extends \Magento\Theme\Block\Html\Topmenu
{
    /**
     * Add sub menu HTML code for current menu item
     *
     * @param Node $child
     * @param string $childLevel
     * @param string $childrenWrapClass
     * @param int $limit
     * @return string HTML code
     */
    protected function _addSubMenu($child, $childLevel, $childrenWrapClass, $limit)
    {
        $html = '';
        if (!$child->hasChildren()) {
            return $html;
        }

        $colStops = [];
        if ($childLevel == 0 && $limit) {
            $colStops = $this->_columnBrake($child->getChildren(), $limit);
        }

        $html .= '<span class="arrow"></span><ul class="dropdown-menu level' . $childLevel . ' ' . $childrenWrapClass . '">';
        $html .= $this->_getHtml($child, $childrenWrapClass, $limit, $colStops);
        $html .= '</ul>';

        return $html;
    }
}
