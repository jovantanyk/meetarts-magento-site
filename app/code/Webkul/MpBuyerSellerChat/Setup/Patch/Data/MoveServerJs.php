<?php declare(strict_types=1);
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_MpBuyerSellerChat
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\MpBuyerSellerChat\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class MoveServerJs implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @param \Magento\Framework\Filesystem\Io\File $filesystem
     * @param \Magento\Framework\Module\Dir\Reader $reader
     * @param DirectoryList $directoryList
     */
    public function __construct(
        \Magento\Framework\Filesystem\Io\File $filesystem,
        \Magento\Framework\Module\Dir\Reader $reader,
        DirectoryList $directoryList
    ) {
        $this->filesystem = $filesystem;
        $this->reader = $reader;
        $this->directoryList = $directoryList;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $rootFullPath = $this->directoryList->getRoot();
        if (!$this->filesystem->fileExists($rootFullPath.'/package.json')) {
            $serverJs = $this->reader->getModuleDir(
                '',
                'Webkul_MpBuyerSellerChat'
            ).'/etc/serverJs/package.json';
            $this->filesystem->cp($serverJs, $rootFullPath.'/package.json');
        }
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }
}
