<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Api\Data;

interface RelevanceRuleInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    public const RULE_ID = 'rule_id';
    public const WEBSITE_ID = 'website_id';
    public const IS_ENABLED = 'is_enabled';
    public const TITLE = 'title';
    public const FROM_DATE = 'from_date';
    public const TO_DATE = 'to_date';
    public const MULTIPLIER = 'multiplier';
    public const CONDITIONS = 'conditions_serialized';
    public const TABLE_NAME = 'amasty_elastic_relevance_rule';
    /**#@-*/

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @return int
     */
    public function getWebsiteId();

    /**
     * @return bool
     */
    public function isEnabled();

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @return string
     */
    public function getFromDate();

    /**
     * @return string
     */
    public function getToDate();

    /**
     * @return int
     */
    public function getMultiplier();

    /**
     * @return string
     */
    public function getConditions();

    /**
     * @param int $websiteId
     * @return RelevanceRuleInterface
     */
    public function setWebsiteId($websiteId);

    /**
     * @param $isEnabled
     * @return RelevanceRuleInterface
     */
    public function setIsEnabled($isEnabled);

    /**
     * @param $title
     * @return RelevanceRuleInterface
     */
    public function setTitle($title);

    /**
     * @param $fromDate
     * @return RelevanceRuleInterface
     */
    public function setFromDate($fromDate);

    /**
     * @param $toDate
     * @return RelevanceRuleInterface
     */
    public function setToDate($toDate);

    /**
     * @param $multiplier
     * @return RelevanceRuleInterface
     */
    public function setMultiplier($multiplier);

    /**
     * @param $condition
     * @return RelevanceRuleInterface
     */
    public function setConditions($condition);

    /**
     * @return \Magento\CatalogRule\Model\Rule
     */
    public function getCatalogRule();

    /**
     * @return bool
     */
    public function isConditionEmpty();
}
