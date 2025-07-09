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

namespace MageMe\WebForms\Ui\Component\Form\Listing\Column;


use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\ResultRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Name extends Column
{
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var ResultRepositoryInterface
     */
    protected $resultRepository;

    /**
     * Results constructor.
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ResultRepositoryInterface $resultRepository
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct
    (
        SearchCriteriaBuilder     $searchCriteriaBuilder,
        ResultRepositoryInterface $resultRepository,
        ContextInterface          $context,
        UiComponentFactory        $uiComponentFactory,
        array                     $components = [],
        array                     $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->resultRepository      = $resultRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritDoc
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $searchCriteria      = $this->searchCriteriaBuilder
                    ->addFilter(ResultInterface::FORM_ID, $item[FormInterface::ID])
                    ->addFilter(ResultInterface::IS_READ, 0)
                    ->create();
                $item['unreadCount'] = $this->resultRepository->getList($searchCriteria)->getTotalCount();
            }
        }
        return $dataSource;
    }
}
