<?php
/*
 * Copyright © Websolute spa. All rights reserved.
 * See LICENSE and/or COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Websolute\TransporterMagentoDefaultStockMsi\Model\Catalog\Product;

use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Model\Indexer\Stock\Action\Row;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Websolute\TransporterBase\Exception\TransporterException;
use Websolute\TransporterMagentoDefaultStockMsi\Model\ResourceModel\SetDefaultStockMsiInterface;

class SetStock
{
    /**
     * @var SetDefaultStockMsiInterface
     */
    private $setDefaultStockMsi;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var Row
     */
    private $indexerRow;

    /**
     * @param SetDefaultStockMsiInterface $setDefaultStockMsi
     * @param ProductFactory $productFactory
     * @param Row $indexerRow
     */
    public function __construct(
        SetDefaultStockMsiInterface $setDefaultStockMsi,
        ProductFactory $productFactory,
        Row $indexerRow
    ) {
        $this->productFactory = $productFactory;
        $this->indexerRow = $indexerRow;
        $this->setDefaultStockMsi = $setDefaultStockMsi;
    }

    /**
     * @param string $sku
     * @param float $quantity
     * @param bool $reindex
     * @throws LocalizedException
     * @throws TransporterException
     * @throws NoSuchEntityException
     */
    public function execute(string $sku, float $quantity, bool $reindex = false)
    {
        $productId = $this->getProductIdFromSku($sku);

        $status = $quantity ? SourceItemInterface::STATUS_IN_STOCK : SourceItemInterface::STATUS_OUT_OF_STOCK;

        $this->setDefaultStockMsi->execute($sku, $quantity, $status);

        if ($reindex) {
            $this->indexerRow->execute($productId);
        }
    }

    /**
     * @param string $sku
     * @return int
     * @throws TransporterException
     */
    private function getProductIdFromSku(string $sku): int
    {
        $product = $this->productFactory->create();
        $product = $product->loadByAttribute('sku', $sku, 'entity_id');
        if (!$product) {
            throw new TransporterException(__('Product with sku %2 does not exist', 'sku', $sku));
        }
        return (int)$product->getId();
    }
}
