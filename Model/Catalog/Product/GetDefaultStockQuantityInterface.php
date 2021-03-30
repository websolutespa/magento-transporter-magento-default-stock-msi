<?php
/*
 * Copyright © Websolute spa. All rights reserved.
 * See LICENSE and/or COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Websolute\TransporterMagentoDefaultStockMsi\Model\Catalog\Product;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;

interface GetDefaultStockQuantityInterface
{
    /**
     * @param string $sku
     * @param float $quantity
     * @return float
     * @throws InputException
     * @throws LocalizedException
     */
    public function execute(string $sku, float $quantity): float;
}
