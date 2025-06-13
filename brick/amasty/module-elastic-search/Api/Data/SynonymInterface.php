<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Api\Data;

interface SynonymInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    public const SYNONYM_ID = 'synonym_id';
    public const TABLE_NAME = 'amasty_elastic_synonym';
    public const STORE_ID = 'store_id';
    public const TERM = 'term';
    /**#@-*/

    /**
     * @return int
     */
    public function getSynonymId();

    /**
     * @param int $synonymId
     *
     * @return \Amasty\ElasticSearch\Api\Data\SynonymInterface
     */
    public function setSynonymId($synonymId);

    /**
     * @return int
     */
    public function getStoreId();

    /**
     * @param int $storeId
     *
     * @return \Amasty\ElasticSearch\Api\Data\SynonymInterface
     */
    public function setStoreId($storeId);

    /**
     * @return string
     */
    public function getTerm();

    /**
     * @param string $term
     *
     * @return \Amasty\ElasticSearch\Api\Data\SynonymInterface
     */
    public function setTerm($term);
}
