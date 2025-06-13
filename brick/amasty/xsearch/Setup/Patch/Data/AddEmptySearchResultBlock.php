<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Setup\Patch\Data;

use Amasty\Base\Helper\Deploy;
use Magento\Cms\Model\Block;
use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\BlockRepository;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\Store;

class AddEmptySearchResultBlock implements DataPatchInterface
{
    public const BLOCK_TITLE = 'Empty Search Results by Amasty';
    public const BLOCK_IDENTIFIER = 'empty-search-results-by-amasty';

    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * @var BlockRepository
     */
    private $blockRepository;

    /**
     * @var Deploy
     */
    private $deploy;

    public function __construct(
        BlockFactory $blockFactory,
        BlockRepository $blockRepository,
        Deploy $deploy
    ) {
        $this->blockFactory = $blockFactory;
        $this->blockRepository = $blockRepository;
        $this->deploy = $deploy;
    }

    /**
     * @return string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return AddEmptySearchResultBlock
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function apply(): AddEmptySearchResultBlock
    {
        $data = [
            Block::TITLE => self::BLOCK_TITLE,
            Block::IDENTIFIER => self::BLOCK_IDENTIFIER,
            Block::IS_ACTIVE => Block::STATUS_ENABLED,
            'stores' => [Store::DEFAULT_STORE_ID],
            Block::CONTENT => <<<CONTENT
<div class="amsearch-emptysearch-cms">
    <div class="amsearch-content amsearch-item">
        <h2 class="amsearch-title">Oops...</h2>
        <p class="amsearch-text">We can't seem to find <br/> the product you're looking for.</p>
        <div class="amsearch-contacts-block">
            <p class="amsearch-title">Need help? Email Us.</p>
            <a class="amsearch-value" href="mailto:{{config path='trans_email/ident_general/email'}}">
                {{config path="trans_email/ident_general/email"}}
            </a>
        </div>
    </div>
    <div class="amsearch-image-block amsearch-item">
        <img src="{{media url='amasty/xsearch/empty-search.png'}}" alt="Empty search" />
    </div>
</div>
CONTENT
        ];

        $pubPath = __DIR__ . '/../../../pub';
        $this->deploy->deployFolder($pubPath);
        try {
            $this->blockRepository->save($this->blockFactory->create(['data' => $data]));
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            //  There is no such media asset with path. need to run php bin/magento media-gallery:sync
            return $this;
        }

        return $this;
    }
}
