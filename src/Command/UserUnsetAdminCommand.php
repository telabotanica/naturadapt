<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UserUnsetAdminCommand extends Command {
	protected static $defaultName = 'user:unset-admin';

	private $manager;

	public function __construct ( EntityManagerInterface $manager ) {
		$this->manager = $manager;

		parent::__construct();
	}

	protected function configure () {
		$this
				->setDescription( 'Set User as ROLE_USER' )
				->setHelp( 'Set User as ROLE_USER' );

		$this
				->addArgument( 'username', InputArgument::REQUIRED, 'The username of the user.' );
	}

	protected function execute ( InputInterface $input, OutputInterface $output ) {
		/**
		 * @var EntityManagerInterface $manager
		 */
		$manager = $this->manager;

		$usersRepository = $manager->getRepository( User::class );

		/**
		 * @var User $user
		 */
		$user = $usersRepository->findOneBy( [ 'email' => $input->getArgument( 'username' ) ] );

		if ( empty( $user ) ) {
			$output->writeln( 'User not found' );

			return;
		}

		$user->setRoles( [ User::ROLE_USER ] );
		$manager->persist( $user );
		$manager->flush();

		$output->writeln( sprintf( 'User %s updated, has roles %s', $user->getDisplayName(), implode( ', ', $user->getRoles() ) ) );
	}
}
