<?php

namespace App\Command;

use App\Entity\User;
use App\Util\Geocoder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCoordinatesCommand extends Command
{
    protected static $defaultName = 'app:update-coordinates';

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
            ->setDescription('Update coordinates and NUTS ID for entities');
    }

    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $output->writeln([
            'Updating coordinates...',
            '=======================',
            '',
        ]);

        $users = $this->entityManager->getRepository(User::class)->findAll();

        foreach ($users as $user) {
            $output->write("Updating coordinates for {$user->getName()}...");

            $coords = $this->geocoder->searchCoords($user->getCity(), $user->getCountry(), $user->getZipcode());

            if ($coords !== null) {
                $user->setLatitude($coords['lat']);
                $user->setLongitude($coords['lng']);


                if($coords['lat'] !== null && $coords['lng'] !== null){
                    $nutsId = $this->geocoder->getNutsId($coords['lat'], $coords['lng']);
                } else {
                    $nutsId = null;
                }

                if ($nutsId !== null) {
                    $user->setRegion($nutsId);
                }

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $output->writeln(' Done.');
            } else {
                $output->writeln(' Failed. Coordinates not found.');
            }
        }

        $output->writeln([
            '',
            '=======================',
            'Coordinates update complete.',
        ]);

    }

}

