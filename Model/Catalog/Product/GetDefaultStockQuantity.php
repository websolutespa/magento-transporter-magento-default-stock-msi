<?php
/*
 * Copyright © Websolute spa. All rights reserved.
 * See LICENSE and/or COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Websolute\TransporterMagentoDefaultStockMsi\Model\Catalog\Product;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Inventory\Model\SourceItem\Command\SourceItemsSave;
use Magento\InventoryApi\Api\GetSourceItemsBySkuInterface;
use Magento\InventoryCatalog\Model\DefaultStockProvider;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;

class GetDefaultStockQuantity implements GetDefaultStockQuantityInterface
{
    /**
     * @var GetSourceItemsBySkuInterface
     */
    private $getSourceItemsBySku;

    /**
     * @var SourceItemsSave
     */
    private $sourceItemsSave;

    /**
     * @var GetProductSalableQtyInterface
     */
    private $getProductSalableQty;

    /**
     * @var DefaultStockProvider
     */
    private $defaultStockProvider;

    /**
     * @param GetSourceItemsBySkuInterface $getSourceItemsBySku
     * @param SourceItemsSave $sourceItemsSave
     * @param GetProductSalableQtyInterface $getProductSalableQty
     * @param DefaultStockProvider $defaultStockProvider
     */
    public function __construct(
        GetSourceItemsBySkuInterface $getSourceItemsBySku,
        SourceItemsSave $sourceItemsSave,
        GetProductSalableQtyInterface $getProductSalableQty,
        DefaultStockProvider $defaultStockProvider
    ) {
        $this->getSourceItemsBySku = $getSourceItemsBySku;
        $this->sourceItemsSave = $sourceItemsSave;
        $this->getProductSalableQty = $getProductSalableQty;
        $this->defaultStockProvider = $defaultStockProvider;
    }

    /**
     * @param string $sku
     * @param float $quantity
     * @return float
     * @throws InputException
     * @throws LocalizedException
     */
    public function execute(string $sku, float $quantity): float
    {
        $sourceItems = $this->getSourceItemsBySku->execute($sku);
        if (empty($sourceItems)) {
            return $quantity;
        }
        $sourceItem = current($sourceItems);
        $stockId = $this->defaultStockProvider->getId();

        $oldSalableQty = $this->getProductSalableQty->execute($sku, $stockId);
        $oldQuantity = $sourceItem->getQuantity() ?: 0.0;
        $delta = $oldQuantity - $oldSalableQty;
        $newQuantity = $quantity - $delta;

        if ($oldQuantity === $newQuantity) {
            return $quantity;
        }

        return $newQuantity;
    }
}
