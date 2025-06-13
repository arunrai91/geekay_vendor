<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Elastic Search Base for Magento 2
 */

namespace Amasty\ElasticSearch\Model\Client\Defaults;

use Amasty\ElasticSearch\Model\Client\ConfigBuilderInterface;
use Magento\Framework\Exception\LocalizedException;

class ConfigBuilder implements ConfigBuilderInterface
{
    public function build(array $clientOptions): array
    {
        if (empty($clientOptions['hostname'])
            || (array_key_exists('enableAuth', $clientOptions) && 1 == $clientOptions['enableAuth']
                && (empty($clientOptions['username']) || empty($clientOptions['password'])))
        ) {
            throw new LocalizedException(__(
                'It is impossible to perform the search. Please make sure Elasticsearch Engine'
                . ' is installed on the server and configured properly.'
            ));
        }

        $host = preg_replace('/http[s]?:\/\//i', '', $clientOptions['hostname']);
        //@codingStandardsIgnoreLine
        $protocol = parse_url($clientOptions['hostname'], PHP_URL_SCHEME) ?: 'http';

        if (!empty($clientOptions['port'])) {
            $host .= ':' . $clientOptions['port'];
        }

        if (!empty($clientOptions['enableAuth']) && ($clientOptions['enableAuth'] == 1)) {
            $host = sprintf('%s:%s@%s', $clientOptions['username'], $clientOptions['password'], $host);
        }

        $host = sprintf('%s://%s', $protocol, $host);
        $clientOptions['hosts'] = [$host];

        return $clientOptions;
    }
}
