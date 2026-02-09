<?php

// src/Command/DeleteExpiredCartCommand.php

namespace App\Command;

use App\Repository\Billing\OrderRepository as OrderRepo;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'delete-expired-cart', description: 'Deletes all expired carts.', )]
// Cron job is run every hour.
#[AsCronTask('@hourly', timezone: 'Asia/Dubai')]
class DeleteExpiredCartCommand extends Command
{
    public function __construct(private readonly OrderRepo $orderRepo)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->orderRepo->deleteExpiredCart();
            $io->success('All expired carts deleted successfully.');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->info('No expired cart deleted.');

            return Command::FAILURE;
        }
    }
}
