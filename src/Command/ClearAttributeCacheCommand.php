<?php

// src/Command/ClearAttributeCacheCommand.php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Cache\CacheInterface;

#[AsCommand(
    name: 'app:cache:clear-attributes',
    description: 'Clear attribute cache after config changes',
)]
class ClearAttributeCacheCommand extends Command
{
    public function __construct(
        private CacheInterface $cache,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            // Clear all attribute-related cache keys
            $patterns = [
                'attributes.*',
                'fullname.*',
                'form.*',
                'raw.*',
                'price_ranges',
            ];

            foreach ($patterns as $pattern) {
                $this->cache->delete($pattern);
            }

            $io->success('Attribute cache cleared successfully!');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Failed to clear cache: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
