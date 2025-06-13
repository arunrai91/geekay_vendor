<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Api\Data;

interface StopWordInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    public const STOP_WORD_ID = 'stop_word_id';
    public const TABLE_NAME = 'amasty_elastic_stop_word';
    public const TERM = 'term';
    public const STORE_ID = 'store_id';
    /**#@-*/

    /**
     * @return int
     */
    public function getStopWordId();

    /**
     * @param int $stopWordId
     *
     * @return \Amasty\ElasticSearch\Api\Data\StopWordInterface
     */
    public function setStopWordId($stopWordId);

    /**
     * @return string
     */
    public function getTerm();

    /**
     * @param string $term
     *
     * @return \Amasty\ElasticSearch\Api\Data\StopWordInterface
     */
    public function setTerm($term);

    /**
     * @return int
     */
    public function getStoreId();

    /**
     * @param int $storeId
     *
     * @return \Amasty\ElasticSearch\Api\Data\StopWordInterface
     */
    public function setStoreId($storeId);
}
