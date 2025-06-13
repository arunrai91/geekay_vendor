<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Console\Command;

use Amasty\Feed\Model\Indexer\LockManager;
use Magento\Framework\Console\Cli;
use Magento\Framework\ObjectManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @deprecated Locking logic is obsoleted.
 * @see \Amasty\Feed\Model\Indexer\LockManager for more details.
 */
class ForceUnlock extends Command
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(
        ObjectManagerInterface $objectManager,
        $name = null
    ) {
        parent::__construct($name);
        $this->objectManager = $objectManager;
    }

    public function getLockManager(): LockManager
    {
        return $this->objectManager->get(LockManager::class);
    }

    protected function configure(): void
    {
        $this->setName('feed:profile:unlock')
            ->setDescription('Force Unlock');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<error>This command is deprecated and cannot be used.</error>');

        return Cli::RETURN_SUCCESS;
    }
}
