<?php

// src/Command/DeactivateUsersCommand.php

namespace App\Command;

use App\Entity\User\UserDeactivate;
use App\Repository\User\UserRepository;
use Doctrine\ORM\EntityManagerInterface as ORM;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// use Zenstruck\ScheduleBundle\Schedule\Task\CommandTask;
// use Zenstruck\ScheduleBundle\Schedule\SelfSchedulingCommand;

/**
 * This command class completes user deactivation.
 *
 * All users wishing to delete their account are retrieved first.
 *
 * The deactivation is then launched for all, once their date of deletion has been reached.
 */
#[AsCommand(
    name: 'deactivate-users',
    description: 'Deactivate users who have opted to delete their account once the date of deletion is reached.',
)]
#[AsCronTask('@hourly', timezone: 'Asia/Dubai')]
class DeactivateUsersCommand extends Command
{
    public function __construct(private readonly ORM $entityManager, private readonly UserRepository $userRepo)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $usersToBeDeactivated = $this->userRepo->usersToBeDeactivated();
        $userCount = 0;

        foreach ($usersToBeDeactivated as $i => $user) {
            /* Prepend 'deleted_ and append a unique ID to
              user identifier.*/
            $identifier = $user->getUserIdentifier();

            // Generate unique ID
            $uniqueId = bin2hex(random_bytes(6));
            $updatedIdentifier = 'deleted_'.$identifier.'_'.$uniqueId;

            $user->setEmail($updatedIdentifier);

            if (empty($user->getUserDeactivate())) {
                $userDeactivate = new UserDeactivate();
            } else {
                $userDeactivate = $user->getUserDeactivate();
            }

            $userDeactivate->setUpdated()
                           ->setDeactivate(true);

            $user->setUserDeactivate($userDeactivate)
                 ->setUpdated();

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            ++$userCount;
        }

        if (0 == $userCount) {
            $output->writeln('No user has been deactivated.');
        } elseif (1 == $userCount) {
            $output->writeln('1 user has been successfully deactivated.');
        } elseif ($userCount > 1) {
            $output->writeln($userCount.' users have been  successfully deactivated.');
        }

        return Command::SUCCESS;
    }
    //
    // /**
    //  * Set schedule for cron job
    //  * In this case, the 'deactivate-users' command will be run every minute.
    //  *
    //  * @param CommandTask $task
    //  */
    // public function schedule(CommandTask $task) : void
    // {
    //     $task->everyMinute()
    //     ;
    // }
}
