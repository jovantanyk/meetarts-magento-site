<?php
/**
 * MageMe
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageMe.com license that is
 * available through the world-wide-web at this URL:
 * https://mageme.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to a newer
 * version in the future.
 *
 * Copyright (c) MageMe (https://mageme.com)
 **/

namespace MageMe\WebForms\Setup;

use MageMe\WebForms\Api\Data\FieldsetInterface;
use MageMe\WebForms\Setup\Table\FieldsetTable;
use MageMe\WebForms\Setup\Table\FormTable;
use MageMe\WebForms\Api\Data\FormInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @inheritdoc
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $formTable = $setup->getTable(FormTable::TABLE_NAME);
        $fieldsetTable = $setup->getTable(FieldsetTable::TABLE_NAME);

        if (version_compare($context->getVersion(), '3.0.12', '<')) {
            $setup->getConnection()->addColumn($formTable, FormInterface::IS_SUCCESS_SESSION_DISPLAYED,
                [
                    Table::OPTION_TYPE => Table::TYPE_BOOLEAN,
                    Table::OPTION_UNSIGNED => true,
                    Table::OPTION_NULLABLE => false,
                    Table::OPTION_DEFAULT => 0,
                    'comment' => 'Add Success session message on redirection'
                ]);
        }

        if (version_compare($context->getVersion(), '3.0.16', '<')) {
            $setup->getConnection()->addColumn($fieldsetTable, FieldsetInterface::IS_LABEL_HIDDEN,
                [
                    Table::OPTION_TYPE => Table::TYPE_BOOLEAN,
                    Table::OPTION_UNSIGNED => true,
                    Table::OPTION_NULLABLE => false,
                    Table::OPTION_DEFAULT => 0,
                    'comment' => 'Hide Label'
                ]);
        }

        if (version_compare($context->getVersion(), '3.0.17', '<')) {
            $setup->getConnection()->addColumn($formTable, FormInterface::ADMIN_NOTIFICATION_SENDER_NAME,
                [
                    Table::OPTION_TYPE => Table::TYPE_TEXT,
                    Table::OPTION_LENGTH => 255,
                    'comment' => 'Admin Notification Sender Name'
                ]);
            $setup->getConnection()->addColumn($formTable, FormInterface::ADMIN_NOTIFICATION_SENDER_EMAIL,
                [
                    Table::OPTION_TYPE => Table::TYPE_TEXT,
                    'comment' => 'Admin Notification Sender Email'
                ]);
        }

        $setup->endSetup();
    }
}