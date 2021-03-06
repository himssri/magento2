<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRuleConfigurable\Plugin\CatalogRule\Model;

class ConfigurableProductsProvider
{
    /** @var \Magento\Framework\App\Resource */
    private $resource;

    /**
     * @param \Magento\Framework\App\Resource $resource
     */
    public function __construct(\Magento\Framework\App\Resource $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @param array $ids
     * @return array
     */
    public function getIds(array $ids)
    {
        $connection = $this->resource->getConnection();
        return $connection->fetchCol(
            $connection
                ->select()
                ->from(['e' => $this->resource->getTableName('catalog_product_entity')], ['e.entity_id'])
                ->where('e.type_id = ?', \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)
                ->where('e.entity_id IN (?)', $ids)
        );
    }
}
