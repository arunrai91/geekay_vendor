<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Export\Adapter;

use Amasty\Feed\Api\Data\FeedInterface;
use Amasty\Feed\Model\Config;
use Amasty\Feed\Model\Config\Source\StorageFolder;
use Amasty\Feed\Model\Export\Utils\ValueModifier;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\ImportExport\Model\Export\Adapter\AbstractAdapter;
use Magento\ImportExport\Model\Export\Adapter\Csv as BaseCsv;

class Csv extends BaseCsv
{
    public const ENCLOSURES = [
        'double_quote' => '"',
        'quote' => '\'',
        'space' => ' ',
        'none' => '/n'
    ];

    /**
     * @var ValueModifier
     */
    protected $valueModifier;

    /**
     * @var string|null
     */
    protected $header;

    /**
     * @var array
     */
    private $csvField = [];

    /**
     * @var bool
     */
    private $columnName;

    /**
     * @var int|null
     */
    private $page;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Config
     */
    private $config;

    public function __construct(
        Filesystem $filesystem,
        Config $config,
        ValueModifier $valueModifier,
        ?string $destination = null,
        ?int $page = null
    ) {
        $this->page = $page;
        $this->filesystem = $filesystem;
        $this->config = $config;
        $this->valueModifier = $valueModifier;
        parent::__construct($filesystem, $destination);
    }

    public function initBasics(FeedInterface $feed): AbstractAdapter
    {
        $delimiters = [
            'comma' => ',',
            'semicolon' => ';',
            'pipe' => '|',
            //phpcs:ignore
            'tab' => chr(9)
        ];

        if ($this->config->getStorageFolder() === StorageFolder::VAR_FOLDER) {
            $dir = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        } else {
            $dir = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        }
        $directoryPath = $dir->getAbsolutePath($this->config->getFilePath());
        if (!$dir->getDriver()->isDirectory($directoryPath)) {
            $dir->getDriver()->createDirectory($directoryPath);
        }

        $this->_directoryHandle = $dir;
        $this->_fileHandler = $dir->openFile($this->_destination, $this->page ? 'a' : 'w');
        $this->_enclosure = self::ENCLOSURES[$feed->getCsvEnclosure()] ?? '"';
        $this->_delimiter = $delimiters[$feed->getCsvDelimiter()] ?? ',';
        $this->columnName = (int)$feed->getCsvColumnName() === 1;
        $this->header = $feed->getCsvHeader();
        $this->csvField = $feed->getCsvField();
        $this->valueModifier->setFeedFormatOptions($feed);

        return $this;
    }

    public function writeHeader(): AbstractAdapter
    {
        $columns = [];
        foreach ($this->csvField as $idx => $field) {
            $this->_headerCols[$idx . '_idx'] = false;
            $columns[] = $field['header'];
        }

        if (!empty($this->header)) {
            $this->_fileHandler->write($this->header . '\\n');
        }

        if ($this->columnName !== false) {
            if ($this->_enclosure === '/n') {
                $this->_fileHandler->write(implode($this->_delimiter, $columns) . '\\n');
            } else {
                $this->_fileHandler->writeCsv($columns, $this->_delimiter, $this->_enclosure);
            }
        }

        return $this;
    }

    public function writeFooter(): AbstractAdapter
    {
        return $this;
    }

    /**
     * @throws LocalizedException
     */
    public function setHeaderCols(array $headerColumns): AbstractAdapter
    {
        if (null !== $this->_headerCols) {
            throw new LocalizedException(__('The header column names are already set.'));
        }
        if ($headerColumns) {
            foreach ($headerColumns as $columnName) {
                $this->_headerCols[$columnName] = false;
            }
        }

        return $this;
    }

    public function writeDataRow(array &$rowData): AbstractAdapter
    {
        $writeRow = [];
        foreach ($this->csvField as $idx => $field) {
            if ($field['static_text']) {
                $value = $field['static_text'];
            } else {
                $fieldKey = $this->getFieldKey($field);
                $value = $rowData[$fieldKey] ?? '';
            }
            $value = $this->modifyValue($field, $value);
            $value = $this->formatValue($field, $value);
            $writeRow[$idx . '_idx'] = $value;
        }

        if (count($writeRow) > 0) {
            if ($this->_enclosure === '/n') {
                foreach ($writeRow as $inx => $val) {
                    $writeRow[$inx] = str_replace($this->_delimiter, '', $val);
                }
                $this->_fileHandler->write(implode($this->_delimiter, $writeRow) . '\\n');
            } else {
                $this->writeRow($writeRow);
            }
        }

        return $this;
    }

    /**
     * Method which caused files deleting on Magento 2.3.5 was redefined
     */
    public function destruct(): void
    {
        if (is_object($this->_fileHandler)) {
            $this->_fileHandler->close();
        }
    }

    /**
     * Disabling original method
     * @see self::initBasics
     */
    protected function _init(): self
    {
        return $this;
    }

    /**
     * @param array $field
     * @param mixed $value
     *
     * @return mixed
     */
    protected function modifyValue(array $field, $value)
    {
        if (isset($field['modify']) && is_array($field['modify'])) {
            foreach ($field['modify'] as $modify) {
                $value = $this->valueModifier->modify(
                    $value,
                    $modify['modify'],
                    $modify['arg0'] ?? null,
                    $modify['arg1'] ?? null
                );
            }
        }

        return $value;
    }

    /**
     * @param array $field
     * @param mixed $value
     *
     * @return mixed
     */
    protected function formatValue(array $field, $value)
    {
        return $this->valueModifier->formatValue($field, $value);
    }

    private function getFieldKey(array $field): string
    {
        $postfix = isset($field['parent']) && $field['parent'] === 'yes'
            ? '|parent'
            : '';

        return $field['attribute'] . $postfix;
    }
}
