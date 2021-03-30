<?php
/*
 * Copyright © Websolute spa. All rights reserved.
 * See LICENSE and/or COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Websolute\TransporterMagentoDefaultStockMsi\Uploader;

use Exception;
use Monolog\Logger;
use Websolute\TransporterBase\Api\UploaderInterface;
use Websolute\TransporterBase\Exception\TransporterException;
use Websolute\TransporterEntity\Api\EntityRepositoryInterface;
use Websolute\TransporterImporter\Model\DotConvention;
use Websolute\TransporterMagentoDefaultStockMsi\Api\DefaultStockMsiConfigInterface;
use Websolute\TransporterMagentoDefaultStockMsi\Model\Catalog\Product\GetDefaultStockQuantityInterface;
use Websolute\TransporterMagentoDefaultStockMsi\Model\Catalog\Product\SetStock;

class DefaultStockMsiUploader implements UploaderInterface
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var EntityRepositoryInterface
     */
    private $entityRepository;

    /**
     * @var DefaultStockMsiConfigInterface
     */
    private $config;

    /**
     * @var GetDefaultStockQuantityInterface
     */
    private $getDefaultStockQuantity;

    /**
     * @var DotConvention
     */
    private $dotConvention;

    /**
     * @var SetStock
     */
    private $setStock;

    /**
     * @var string
     */
    private $field;

    /**
     * @var bool
     */
    private $mandatory;

    /**
     * @param Logger $logger
     * @param EntityRepositoryInterface $entityRepository
     * @param DefaultStockMsiConfigInterface $config
     * @param GetDefaultStockQuantityInterface $getDefaultStockQuantity
     * @param DotConvention $dotConvention
     * @param SetStock $setStock
     * @param string $field
     * @param bool $mandatory
     */
    public function __construct(
        Logger $logger,
        EntityRepositoryInterface $entityRepository,
        DefaultStockMsiConfigInterface $config,
        GetDefaultStockQuantityInterface $getDefaultStockQuantity,
        DotConvention $dotConvention,
        SetStock $setStock,
        string $field,
        bool $mandatory = true
    ) {
        $this->logger = $logger;
        $this->entityRepository = $entityRepository;
        $this->config = $config;
        $this->getDefaultStockQuantity = $getDefaultStockQuantity;
        $this->dotConvention = $dotConvention;
        $this->setStock = $setStock;
        $this->field = $field;
        $this->mandatory = $mandatory;
    }

    /**
     * @param int $activityId
     * @param string $uploaderType
     * @throws TransporterException
     */
    public function execute(int $activityId, string $uploaderType): void
    {
        $allActivityEntities = $this->entityRepository->getAllDataManipulatedByActivityIdGroupedByIdentifier($activityId);

        $i = 0;
        $tot = count($allActivityEntities);
        foreach ($allActivityEntities as $entityIdentifier => $entities) {

            try {
                $quantity = (float)$this->dotConvention->getValue($entities, $this->field);
            } catch (TransporterException $exception) {
                if ($this->mandatory) {
                    throw $exception;
                }
                continue;
            }

            $this->logger->info(__(
                'activityId:%1 ~ Uploader ~ uploaderType:%2 ~ entityIdentifier:%3 ~ step:%4/%5 ~ START',
                $activityId,
                $uploaderType,
                $entityIdentifier,
                ++$i,
                $tot
            ));

            try {

                $sku = $entityIdentifier;
                $reindex = $this->config->isReindexAfterImport();

                $realQuantity = $this->getDefaultStockQuantity->execute($sku, $quantity);

                $this->setStock->execute($sku, $realQuantity, $reindex);

                $this->logger->info(__(
                    'activityId:%1 ~ Uploader ~ uploaderType:%2 ~ entityIdentifier:%3 ~ new stock value:%4 ~ real quantity:%5 ~ END',
                    $activityId,
                    $uploaderType,
                    $entityIdentifier,
                    $quantity,
                    $realQuantity
                ));
            } catch (Exception $e) {
                $this->logger->error(__(
                    'activityId:%1 ~ Uploader ~ uploaderType:%2 ~ entityIdentifier:%3 ~ ERROR ~ error:%4',
                    $activityId,
                    $uploaderType,
                    $entityIdentifier,
                    $e->getMessage()
                ));

                if (!$this->config->continueInCaseOfErrors()) {
                    throw new TransporterException(__(
                        'activityId:%1 ~ Uploader ~ uploaderType:%2 ~ entityIdentifier:%3 ~ END ~ Because of continueInCaseOfErrors = false',
                        $activityId,
                        $uploaderType,
                        $entityIdentifier
                    ));
                }
            }
        }
    }
}
