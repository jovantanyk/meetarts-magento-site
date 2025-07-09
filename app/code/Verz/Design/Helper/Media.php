<?php
/**
 * @package   Verz_Design
 * @copyright Copyright (c) 2022 VerzDesign (https://www.verzdesign.com/)
 * @contacts  enquiry@verzdesign.com
 */

namespace Verz\Design\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Area;
use Magento\Catalog\Helper\Image;

class Media extends \Magento\Swatches\Helper\Media
{
    protected function setupImageProperties(\Magento\Framework\Image $image, $isSwatch = false)
    {
        $image->quality(100);
        $image->constrainOnly(true);
        $image->keepAspectRatio(true);
        $image->keepTransparency(true);
        if ($isSwatch) {
            $image->keepFrame(true);
        }
        return $this;
    }
}