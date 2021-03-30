<?php
/*
 * Copyright © Websolute spa. All rights reserved.
 * See LICENSE and/or COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Websolute\TransporterMagentoDefaultStockMsi\Model\ResourceModel;

use Magento\Framework\Exception\NoSuchEntityException;

interface SetDefaultStockMsiInterface
{
    /**
     * @param string $sku
     * @param float $quantity
     * @param int $status
     * @return bool $isChanged
     * @throws NoSuchEntityException
     */
    public function execute(string $sku, float $quantity, int $status): bool;
}
