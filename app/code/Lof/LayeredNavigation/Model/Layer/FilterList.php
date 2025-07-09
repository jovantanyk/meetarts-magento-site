<?php
namespace Lof\LayeredNavigation\Model\Layer;

class FilterList {

    /**
     * @param \Magento\Catalog\Model\Layer $layer
     * @param array|Filter\AbstractFilter[]
     * @return array
     */
    public function afterGetFilters(\Magento\Catalog\Model\Layer $layer){

        if (!count($this->filters)) {
            $this->filters = [
                $this->objectManager->create($this->filterTypes[self::CATEGORY_FILTER], ['layer' => $layer]),
            ];
            foreach ($this->filterableAttributes->getList() as $attribute) {
                $this->filters[] = $this->createAttributeFilter($attribute, $layer);
            }
        }
        $data = array_shift($this->filters);
        $this->filters[]=  $data;
        return $this->filters;
    }

}