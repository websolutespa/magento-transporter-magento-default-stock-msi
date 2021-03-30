<?php
/*
 * Copyright © Websolute spa. All rights reserved.
 * See LICENSE and/or COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Websolute_TransporterMagentoDefaultStockMsi',
    __DIR__
);
