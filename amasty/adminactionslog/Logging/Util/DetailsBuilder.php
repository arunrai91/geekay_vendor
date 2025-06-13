<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Logging\Util;

use Amasty\AdminActionsLog\Model\LogEntry;

class DetailsBuilder
{
    /**
     * @var ClassNameNormalizer
     */
    private $classNameNormalizer;

    /**
     * @var LogEntry\LogDetailFactory
     */
    private $detailFactory;

    public function __construct(
        ClassNameNormalizer $classNameNormalizer,
        LogEntry\LogDetailFactory $detailFactory
    ) {
        $this->classNameNormalizer = $classNameNormalizer;
        $this->detailFactory = $detailFactory;
    }

    /**
     * @param string $modelName
     * @param array $beforeData
     * @param array $afterData
     * @return LogEntry\LogDetail[]
     */
    public function build(string $modelName, array $beforeData, array $afterData): array
    {
        $detailList = [];
        $allDataKeys = array_keys(array_merge($beforeData, $afterData));

        foreach ($allDataKeys as $dataKey) {
            $oldValue = $beforeData[$dataKey] ?? null;
            $newValue = $afterData[$dataKey] ?? null;

            if ($oldValue != $newValue) {
                $detailList[] = $this->detailFactory->create(['data' => [
                    LogEntry\LogDetail::MODEL => $this->classNameNormalizer->execute($modelName),
                    LogEntry\LogDetail::NAME => $dataKey,
                    LogEntry\LogDetail::OLD_VALUE => $oldValue,
                    LogEntry\LogDetail::NEW_VALUE => $newValue,
                ]]);
            }
        }

        return $detailList;
    }
}
