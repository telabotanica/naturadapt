<?php

namespace App\Command;

use App\Entity\User;
use App\Util\Geocoder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateNutsIdCommand extends Command
{
    protected static $defaultName = 'app:update-nuts-id';

    private $entityManager;
    private $geocoder;

    public function __construct(EntityManagerInterface $entityManager, Geocoder $geocoder)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->geocoder = $geocoder;
    }

    protected function configure()
    {
        $this
            ->setDescription('Update NUTS ID for entities');
    }

    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $output->writeln([
            'Updating NUTS ID...',
            '=======================',
            '',
        ]);

        $users = $this->entityManager->getRepository(User::class)->findAll();

        foreach ($users as $user) {
            $output->write("Updating NUTS ID for {$user->getName()}...");

            if ($user->getLatitude() !== null && $user->getLongitude() !== null) {
                $nutsId = $this->geocoder->getNutsId($user->getLatitude(), $user->getLongitude());
            } else {
                $nutsId = null;
            }

            if ($nutsId !== null) {
                $user->setRegion($nutsId);

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $output->writeln(' Done.');
            } else {
                $output->writeln(' Failed. NUTS ID not found.');
            }
        }

        $output->writeln([
            '',
            '=======================',
            'NUTS ID update complete.',
        ]);
    }
}
