<?php declare(strict_types=1);
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpBuyerSellerChat
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpBuyerSellerChat\Model\Config;

/**
 * Generic source
 */
class BackgroundType
{
    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        $options = [
            ['value'=>'', 'label'=>'Select Background Type'],
            ['value'=>'color', 'label'=>'Solid Color'],
            ['value'=>'image', 'label'=>'Image'],
        ];
        
        return $options;
    }
}
