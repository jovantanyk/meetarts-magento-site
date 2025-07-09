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
namespace Webkul\MarketplaceEventManager\Model;

class Qrgenerator
{
    /**
     * Generate
     *
     * @param string $data
     * @param string $size
     * @param string $encoding
     * @param string $errorCorrectionLevel
     * @param int $marginInRows
     * @param bool $debug
     */
    public function generate(
        $data = '',
        $size = '300',
        $encoding = 'UTF-8',
        $errorCorrectionLevel = 'L',
        $marginInRows = 4,
        $debug = false
    ) {
         $data = urlencode($data);
        $size = ($size>100 && $size<800)? $size : 300;
        $encoding = ($encoding == 'Shift_JIS'
            || $encoding == 'ISO-8859-1'
            || $encoding == 'UTF-8') ? $encoding : 'UTF-8';
        $errorCorrectionLevel = ($errorCorrectionLevel == 'L'
            || $errorCorrectionLevel == 'M'
            || $errorCorrectionLevel == 'Q'
            || $errorCorrectionLevel == 'H') ?  $errorCorrectionLevel : 'L';

        $marginInRows=($marginInRows>0 && $marginInRows<10) ? $marginInRows:4;
        $debug = ($debug==true)? true:false;
        $QRLink = "https://chart.googleapis.com/chart?cht=qr&chs=".$size."x".$size."&chl=" . $data .
                   "&choe=" . $encoding .
                   "&chld=" . $errorCorrectionLevel . "|" . $marginInRows;
        return $QRLink;
    }
}
