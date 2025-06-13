<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Integration\Model\ResourceModel\Page\Fulltext;

use Amasty\Xsearch\Model\ResourceModel\Page\Fulltext\Collection;
use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Model\PageFactory;
use Magento\Cms\Model\ResourceModel\Page;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppIsolation disabled
 * @magentoDbIsolation disabled
 */
class CollectionSortingTest extends TestCase
{
    /**
     * @var Collection
     */
    private $testObject;

    /**
     * @var Page
     */
    private $pageResource;

    /**
     * @var PageFactory
     */
    private $pageFactory;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->testObject = $objectManager->create(Collection::class);
        $this->pageResource = $objectManager->get(Page::class);
        $this->pageFactory = $objectManager->get(PageFactory::class);
    }

    /**
     * @dataProvider cmsDataProvider
     */
    public function testSorting(array $pageData, string $query, array $expectedIdentifiers): void
    {
        $this->addCmsPages($pageData);
        $collection = $this->testObject;
        $collection->resetData();
        $collection->clear();
        /**
         * emulate behavior of
         * @see \Amasty\Xsearch\Block\Search\Page::generateCollection
         */
        $collection->addSearchFilter($query);
        $collection->addStoreFilter(1);
        $collection->addFieldToFilter(PageInterface::IS_ACTIVE, 1);
        $collection->setPageSize(4);
        // end emulation

        $collection->addFieldToFilter(PageInterface::IDENTIFIER, ['like' => 'xsearch_%']);
        $resultIdentifiers = [];
        foreach ($collection->getItems() as $item) {
            $resultIdentifiers[] = $item->getIdentifier();
            $this->pageResource->delete($item);
        }

        self::assertEquals($expectedIdentifiers, $resultIdentifiers, 'Sorting test failure');
    }

    private function addCmsPages(array $pageData): void
    {
        foreach ($pageData as $row) {
            $page = $this->pageFactory->create();
            $page->setData($row);
            $this->pageResource->save($page);
        }
    }

    private function cmsDataProvider(): array
    {
        return [
            'title to top' => [
                [ // CMS Pages data
                    [
                        PageInterface::IDENTIFIER => 'xsearch_test1_asdas',
                        PageInterface::TITLE => 'Content',
                        PageInterface::CONTENT => '<p>asdas asdaszxczxc asdas asdas asdas</p>',
                        PageInterface::META_KEYWORDS => 'asdas asdas',
                        PageInterface::META_DESCRIPTION => 'asdas asdas',
                        PageInterface::IS_ACTIVE => 1,
                        PageInterface::SORT_ORDER => 1,
                        PageInterface::PAGE_LAYOUT => '1column',
                        'store_id' => '1',
                        'stores' => [1],
                    ],
                    [
                        PageInterface::IDENTIFIER => 'xsearch_test1_1',
                        PageInterface::TITLE => 'asdaszxxs top',
                        PageInterface::CONTENT => '<p>Content keyword</p>',
                        PageInterface::META_KEYWORDS => 'miss',
                        PageInterface::META_DESCRIPTION => 'nope',
                        PageInterface::IS_ACTIVE => 1,
                        PageInterface::SORT_ORDER => 500,
                        PageInterface::PAGE_LAYOUT => '1column',
                        'store_id' => '1',
                        'stores' => [1],
                    ]
                ],
                'asdas', // search query
                [ // expected result
                    'xsearch_test1_1',
                    'xsearch_test1_asdas',
                ],
            ],
            'wildcard match' => [
                [
                    [
                        PageInterface::IDENTIFIER => 'xsearch_test2_1',
                        PageInterface::TITLE => 'Content',
                        PageInterface::CONTENT => '<p>miss miss miss</p>',
                        PageInterface::META_KEYWORDS => 'keywordy',
                        PageInterface::META_DESCRIPTION => 'miss',
                        PageInterface::IS_ACTIVE => 1,
                        PageInterface::SORT_ORDER => 1,
                        PageInterface::PAGE_LAYOUT => '1column',
                        'store_id' => '1',
                        'stores' => [1],
                    ],
                    [
                        PageInterface::IDENTIFIER => 'xsearch_test2_2',
                        PageInterface::TITLE => 'Content',
                        PageInterface::CONTENT => '<p>miss miss miss</p>',
                        PageInterface::META_KEYWORDS => 'keywordy keywordy',
                        PageInterface::META_DESCRIPTION => '',
                        PageInterface::IS_ACTIVE => 1,
                        PageInterface::SORT_ORDER => 500,
                        PageInterface::PAGE_LAYOUT => '1column',
                        'store_id' => '1',
                        'stores' => [1],
                    ]
                ],
                'keyword',
                [
                    'xsearch_test2_2',
                    'xsearch_test2_1',
                ],
            ],
            'strict match' => [
                [
                    [
                        PageInterface::IDENTIFIER => 'xsearch_test3_1',
                        PageInterface::TITLE => 'Content',
                        PageInterface::CONTENT => '<p>miss miss miss</p>',
                        PageInterface::META_KEYWORDS => ' keyword',
                        PageInterface::META_DESCRIPTION => 'miss',
                        PageInterface::IS_ACTIVE => 1,
                        PageInterface::SORT_ORDER => 1,
                        PageInterface::PAGE_LAYOUT => '1column',
                        'store_id' => '1',
                        'stores' => [1],
                    ],
                    [
                        PageInterface::IDENTIFIER => 'xsearch_test3_2',
                        PageInterface::TITLE => 'Content',
                        PageInterface::CONTENT => '<p></p>',
                        PageInterface::META_KEYWORDS => 'keyword keyword',
                        PageInterface::META_DESCRIPTION => '',
                        PageInterface::IS_ACTIVE => 1,
                        PageInterface::SORT_ORDER => 500,
                        PageInterface::PAGE_LAYOUT => '1column',
                        'store_id' => '1',
                        'stores' => [1],
                    ]
                ],
                'keyword',
                [
                    'xsearch_test3_2',
                    'xsearch_test3_1',
                ],
            ],
        ];
    }
}
