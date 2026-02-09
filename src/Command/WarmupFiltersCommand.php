<?php

// src/Command/WarmupFiltersCommand.php

namespace App\Command;

use App\Service\MaxItemsService;
use App\Service\StaticFiltersService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'cache:warmup-filters',
    description: 'Warm up filter and max items caches',
)]
class WarmupFiltersCommand extends Command
{
    public function __construct(
        private StaticFiltersService $staticFilters,
        private MaxItemsService $maxItemsService,
        private LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Warming up filter and max items caches');

        try {
            // Warmup static filters
            $io->section('Warming up static filters...');
            $startTime = microtime(true);

            $success = $this->staticFilters->warmUpCache();

            if ($success) {
                $executionTime = microtime(true) - $startTime;
                $io->success(sprintf(
                    'Static filters cache warmed up successfully (%.2fms)',
                    $executionTime * 1000
                ));
            } else {
                $io->warning('Static filters cache warmup encountered issues');
            }

            // Warmup max items
            $io->section('Warming up max items cache...');
            $startTime = microtime(true);

            $maxItems = $this->maxItemsService->listing();
            $executionTime = microtime(true) - $startTime;

            $io->success(sprintf(
                'Max items cache warmed up (listing: %d items, %.2fms)',
                $maxItems,
                $executionTime * 1000
            ));

            // Display summary
            $io->section('Cache Summary');
            $summary = $this->staticFilters->getFilterSummary();

            $rows = [];
            foreach ($summary as $type => $count) {
                $rows[] = [$type, $count];
            }
            $rows[] = ['Max items per page', $maxItems];

            $io->table(['Filter Type', 'Count'], $rows);

            $this->logger->info('Cache warmup completed successfully', [
                'summary' => $summary,
                'maxItems' => $maxItems,
            ]);

            $io->success('All caches warmed up successfully!');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('Error warming up caches: '.$e->getMessage());

            $this->logger->error('Cache warmup failed', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }
}
