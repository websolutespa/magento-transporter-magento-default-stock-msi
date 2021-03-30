<?php
/*
 * Copyright © Websolute spa. All rights reserved.
 * See LICENSE and/or COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Websolute\TransporterMagentoDefaultStockMsi\Model\ResourceModel;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Validation\ValidationException;
use Magento\Inventory\Model\SourceItem\Command\SourceItemsSave;
use Magento\InventoryApi\Api\GetSourceItemsBySkuInterface;
use Magento\InventoryCatalog\Model\DefaultStockProvider;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;

class SetDefaultStockMsi implements SetDefaultStockMsiInterface
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
     * @param int $status
     * @return bool $isChanged
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws ValidationException
     */
    public function execute(string $sku, float $quantity, int $status): bool
    {
        $sourceItems = $this->getSourceItemsBySku->execute($sku);
        if (empty($sourceItems)) {
            return false;
        }

        $sourceItem = current($sourceItems);
        $sourceItem->setQuantity($quantity);
        $sourceItem->setStatus($status);

        $this->sourceItemsSave->execute([$sourceItem]);

        return true;
    }
}
