<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Export\Adapter;

use Amasty\Feed\Api\Data\FeedInterface;
use Amasty\Feed\Model\OptionSource\Feed\ParentFlag;
use Magento\Framework\Filesystem\File\Write;
use Magento\ImportExport\Model\Export\Adapter\AbstractAdapter;

class Xml extends Csv
{
    /**
     * Index of result array that consists of strings matched by the first parenthesized subpattern
     * @see http://php.net/manual/function.preg-match-all.php
     */
    public const PREG_FIRST_SUBMASK = 1;
    public const DATE_DIRECTIVE = '{{DATE}}';

    /**
     * @var Write
     */
    protected $_fileHandler;

    /**
     * @var string|null
     */
    protected $header;

    /**
     * @var string
     */
    private $item;

    /**
     * @var string
     */
    private $content;

    /**
     * @var array
     */
    private $contentAttributes;

    /**
     * @var string|null
     */
    private $footer;

    /**
     * @var string[]
     */
    private $tagsToRemove = [
        'g:additional_image_link',
        'g:sale_price_effective_date'
    ];

    /**
     * @var string[]
     */
    private $currDateReplacements = [
        'created_at' => 'Y-m-d H:i',
        'lastBuildDate' => 'D M d H:i:s Y'
    ];

    /**
     * MIME-type for 'Content-Type' header.
     * @return string
     */
    public function getContentType(): string
    {
        return 'text/xml';
    }

    /**
     * Return file extension for downloading.
     * @return string
     */
    public function getFileExtension(): string
    {
        return 'xml';
    }

    public function initBasics(FeedInterface $feed): AbstractAdapter
    {
        parent::initBasics($feed);
        $this->header = $feed->getXmlHeader();
        $this->item = $feed->getXmlItem();
        $this->footer = $feed->getXmlFooter();
        $this->parseContent($feed->getXmlContent());

        return $this;
    }

    public function writeHeader(): AbstractAdapter
    {
        if (!empty($this->header)) {
            $header = $this->header;
            foreach ($this->currDateReplacements as $tagName => $dateFormat) {
                $openTag = '<' . $tagName . '>';
                $closeTag = '</' . $tagName . '>';
                $header = str_replace(
                    $openTag . self::DATE_DIRECTIVE . $closeTag,
                    $openTag . date($dateFormat) . $closeTag,
                    $header
                ) . PHP_EOL;
            }
            $this->_fileHandler->write($header);
        }

        return $this;
    }

    public function writeFooter(): AbstractAdapter
    {
        if (!empty($this->footer)) {
            $this->_fileHandler->write($this->footer);
        }

        return $this;
    }

    public function writeDataRow(array &$rowData): AbstractAdapter
    {
        $replace = $this->prepareReplace($rowData);

        $write = '';
        if ($this->item) {
            $write .= '<' . $this->item . '>' . PHP_EOL;
        }
        $writeItem = strtr($this->content, $replace);

        $tags = array_unique($this->tagsToRemove);
        foreach ($tags as $tag) {
            $this->clearEmptyTag($writeItem, $tag);
        }

        $write .= $writeItem;
        if ($this->item) {
            $write .= PHP_EOL . '</' . $this->item . '>' . PHP_EOL;
        }
        $this->_fileHandler->write($write);

        return $this;
    }

    /**
     * @param array $field
     * @param mixed $value
     * @return mixed
     */
    protected function modifyValue(array $field, $value)
    {
        if (!empty($field['modify'])) {
            foreach (explode('|', $field['modify']) as $modify) {
                $modifyArr = explode(':', $modify, 2);
                $modifyType = $modifyArr[0];
                $arg0 = $arg1 = null;
                if (isset($modifyArr[1])) {
                    $modifyArgs = explode('^', $modifyArr[1]);
                    if (isset($modifyArgs[0])) {
                        $arg0 = $modifyArgs[0];
                    }
                    if (isset($modifyArgs[1])) {
                        $arg1 = $modifyArgs[1];
                    }
                }
                $value = $this->valueModifier->modify($value, $modifyType, $arg0, $arg1);
            }
        }

        return $value;
    }

    /**
     * Add CDATA
     * @param array $field
     * @param mixed $value
     * @return mixed
     */
    protected function formatValue(array $field, $value)
    {
        $ret = parent::formatValue($field, $value);
        if (!empty($field['modify']) && !empty($ret) && !is_int($value)) {
            $ret = '<![CDATA[' . $ret . ']]>';
        }

        return $ret;
    }

    private function parseContent(string $content): void
    {
        preg_match_all('#{(.*?)}#', $content, $vars);
        $contentAttributes = [];
        if (isset($vars[self::PREG_FIRST_SUBMASK])) {
            foreach ($vars[self::PREG_FIRST_SUBMASK] as $attributeRow) {
                $attributeParams = [];
                preg_match('/attribute="(.*?)"/', $attributeRow, $attrReg);
                preg_match('/format="(.*?)"/', $attributeRow, $formatReg);
                preg_match('/modify="(.*?)"/', $attributeRow, $lengthReg);
                preg_match('/parent="(.*?)"/', $attributeRow, $parentReg);
                if (isset($attrReg[self::PREG_FIRST_SUBMASK])) {
                    $attributeParams = [
                        'attribute' => $attrReg[self::PREG_FIRST_SUBMASK] ?? '',
                        'format' => $formatReg[self::PREG_FIRST_SUBMASK] ?? 'as_is',
                        'modify' => $lengthReg[self::PREG_FIRST_SUBMASK] ?? '',
                        'parent' => $parentReg[self::PREG_FIRST_SUBMASK] ?? 'no',
                    ];
                }
                $contentAttributes[$attributeRow] = $attributeParams;
            }
        }
        $this->setTagsToDeleteFromContent($content);
        $this->content = $content;
        $this->contentAttributes = $contentAttributes;
    }

    private function prepareReplace(array $rowData): array
    {
        $replace = [];
        if (is_array($this->contentAttributes)) {
            foreach ($this->contentAttributes as $search => $attribute) {
                $code = $attribute['attribute'];
                $value = $rowData[$code] ?? null;
                if (array_key_exists('parent', $attribute)) {
                    $parentValue = $rowData[$code . '|parent'] ?? null;
                    switch ($attribute['parent']) {
                        case ParentFlag::YES:
                            $value = $parentValue ?? $value;
                            break;
                        case ParentFlag::YES_IF_EMPTY:
                            $value = $value ?? $parentValue;
                            break;
                        case ParentFlag::YES_STRICT:
                            $value = $value !== $parentValue
                                ? $rowData[$code . '|parent']
                                : '';
                            break;
                    }
                }

                $value = $value ?? '';
                $value = $this->modifyValue($attribute, $value);
                $value = $this->formatValue($attribute, $value);
                $replace['{' . $search . '}'] = $value;
            }
        }

        return $replace;
    }

    private function clearEmptyTag(string &$content = '', string $tag = ''): void
    {
        $pattern = '~<' . $tag . '></' . $tag . '>' . "\r?\n?~";
        $content = preg_replace($pattern, '', $content);
    }

    private function setTagsToDeleteFromContent(string $content): void
    {
        $regex = '/<(.*)>.*optional="yes".*<\/(.*)>/';
        preg_match_all($regex, $content, $matches);
        if (isset($matches[self::PREG_FIRST_SUBMASK])) {
            $this->tagsToRemove = array_merge($this->tagsToRemove, $matches[1]);
        }
    }
}
