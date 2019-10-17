<?php

namespace App\Controller;

use App\Entity\File;
use App\Entity\LogEvent;
use App\Entity\Site;
use App\Entity\User;
use App\Entity\UsergroupMembership;
use App\Form\UserEmailType;
use App\Form\UserPasswordType;
use App\Form\UserProfileType;
use App\Security\UserVoter;
use App\Service\Community;
use App\Service\EmailSender;
use App\Service\FileManager;
use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class UserController extends AbstractController {
	/**
	 * Login form can be embed in pages
	 *
	 * @param \Symfony\Component\Security\Http\Authentication\AuthenticationUtils $authenticationUtils
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function loginForm ( AuthenticationUtils $authenticationUtils ) {
		$error        = $authenticationUtils->getLastAuthenticationError();
		$lastUsername = $authenticationUtils->getLastUsername();

		if ( !empty( $error ) ) {
			$key = $error->getMessageKey();
			if ( $key === 'Invalid credentials.' ) {
				$key = 'messages.user.invalid_credentials';
			}

			$this->addFlash( 'error', $key );
		}

		return $this->render( 'forms/user/login.html.twig', [
				'last_username' => $lastUsername,
		] );
	}

	/**
	 * @Route("/user/login", name="user_login")
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function loginPage () {
		if ( $this->isGranted( UserVoter::LOGGED ) ) {
			$this->addFlash( 'notice', 'messages.user.already_connected' );

			return $this->redirectToRoute( 'homepage' );
		}

		return $this->render( 'pages/user/login.html.twig' );
	}

	/**
	 * @Route("/user/logout", name="user_logout")
	 */
	public function logout () {
		return $this->redirectToRoute( 'homepage' );
	}

	/**
	 * @Route("/user/register", name="user_register")
	 *
	 * @param \Symfony\Component\HttpFoundation\Request                               $request
	 * @param \Doctrine\Common\Persistence\ObjectManager                              $manager
	 * @param \App\Service\EmailSender                                                $mailer
	 * @param \Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface $tokenGenerator
	 * @param \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface   $passwordEncoder
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @throws \Exception
	 */
	public function register (
			Request $request,
			ObjectManager $manager,
			EmailSender $mailer,
			TokenGeneratorInterface $tokenGenerator,
			UserPasswordEncoderInterface $passwordEncoder
	) {
		if ( $request->isMethod( 'POST' ) && ( $request->request->get( 'action' ) === 'register' ) ) {
			$userRepository = $manager->getRepository( User::class );

			if ( $userRepository->findOneBy( [ 'email' => $request->request->get( 'email' ) ] ) ) {
				$this->addFlash( 'error', 'messages.user.exists' );

				return $this->redirectToRoute( 'user_login' );
			}

			$user = new User();
			$user->setCreatedAt( new DateTime() );
			$user->setEmail( $request->request->get( 'email' ) );
			$user->setPassword( $passwordEncoder->encodePassword( $user, $request->request->get( 'password' ) ) );
			$user->setRoles( [ User::ROLE_USER ] );
			$user->setStatus( User::STATUS_PENDING );

			$token = $tokenGenerator->generateToken();
			$user->setResetToken( $token );

			$manager->persist( $user );

			// Log Event

			$log = new LogEvent();
			$log->setType( LogEvent::USER_REGISTER );
			$log->setUser( $user );
			$log->setCreatedAt( new \DateTime() );
			$manager->persist( $log );

			// --

			$manager->flush();

			$message = $this->renderView( 'emails/register-activation.html.twig', [
					'user' => $user,
					'url'  => $this->generateUrl( 'user_activate', array( 'token' => $token ), UrlGeneratorInterface::ABSOLUTE_URL ),
			] );

			$mailer->send(
					$this->getParameter( 'plateform' )[ 'from' ],
					$user->getEmail(),
					$mailer->getSubjectFromTitle( $message ),
					$message
			);

			$this->addFlash( 'notice', 'messages.user.activation_sent' );

			return $this->redirectToRoute( 'homepage' );
		}

		return $this->redirectToRoute( 'user_login' );
	}

	/**
	 * @Route("/user/activate/{token}", name="user_activate")
	 *
	 * @param \Symfony\Component\HttpFoundation\Request                                           $request
	 * @param string                                                                              $token
	 * @param \Doctrine\Common\Persistence\ObjectManager                                          $manager
	 * @param \Symfony\Component\HttpFoundation\Session\SessionInterface                          $session
	 * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
	 * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface                         $eventDispatcher
	 *
	 * @param \App\Service\Community                                                              $community
	 *
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function activate (
			Request $request,
			string $token,
			ObjectManager $manager,
			SessionInterface $session,
			TokenStorageInterface $tokenStorage,
			EventDispatcherInterface $eventDispatcher,
			Community $community
	) {
		/**
		 * @var $user User
		 */
		$user = $manager->getRepository( User::class )->findOneBy( [ 'resetToken' => $token ] );

		if ( $user === NULL ) {
			$this->addFlash( 'error', 'messages.user.activation_token_unknown' );

			return $this->redirectToRoute( 'homepage' );
		}

		if ( $user->getStatus() === User::STATUS_ACTIVE ) {
			$this->addFlash( 'notice', 'messages.user.activation_already_active' );

			return $this->redirectToRoute( 'homepage' );
		}

		if ( $user->getStatus() !== User::STATUS_PENDING ) {
			$this->addFlash( 'warning', 'messages.user.activation_impossible' );

			return $this->redirectToRoute( 'homepage' );
		}

		$user->setResetToken( NULL );
		$user->setStatus( User::STATUS_ACTIVE );

		// Join community

		if ( $community->getGroup() ) {
			$membership = new UsergroupMembership();
			$membership->setUsergroup( $community->getGroup() );
			$membership->setUser( $user );
			$membership->setRole( UsergroupMembership::ROLE_USER );
			$membership->setStatus( UsergroupMembership::STATUS_MEMBER );
			$membership->setJoinedAt( new \DateTime() );

			$manager->persist( $membership );
		}

		//

		$manager->flush();

		// Manual login

		$token = new UsernamePasswordToken( $user, NULL, 'main', $user->getRoles() );
		$tokenStorage->setToken( $token );
		$session->set( '_security_main', serialize( $token ) );
		$event = new InteractiveLoginEvent( $request, $token );
		$eventDispatcher->dispatch( 'security.interactive_login', $event );

		// Redirect to profile

		$this->addFlash( 'notice', 'messages.user.activation_successful' );

		return $this->redirectToRoute( 'user_profile_create' );
	}

	/**
	 * @Route("/user/password", name="user_forgotten_password")
	 *
	 * @param \Symfony\Component\HttpFoundation\Request                               $request
	 * @param \Doctrine\Common\Persistence\ObjectManager                              $manager
	 * @param \App\Service\EmailSender                                                $mailer
	 * @param \Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface $tokenGenerator
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function forgottenPassword (
			Request $request,
			ObjectManager $manager,
			EmailSender $mailer,
			TokenGeneratorInterface $tokenGenerator
	) {
		if ( $request->isMethod( 'POST' ) ) {
			$email = $request->request->get( 'email' );
			/**
			 * @var $user User
			 */
			$user = $manager->getRepository( User::class )->findOneBy( [ 'email' => $email ] );

			if ( $user === NULL ) {
				$this->addFlash( 'warning', 'messages.user.unknown' );

				return $this->redirectToRoute( 'homepage' );
			}

			if ( $user->getStatus() !== User::STATUS_ACTIVE ) {
				$this->addFlash( 'error', 'messages.user.inactive' );

				return $this->redirectToRoute( 'homepage' );
			}

			$token = $tokenGenerator->generateToken();

			try {
				$user->setResetToken( $token );
				$manager->flush();
			} catch ( Exception $e ) {
				$this->addFlash( 'warning', $e->getMessage() );

				return $this->redirectToRoute( 'homepage' );
			}

			$message = $this->renderView( 'emails/forgotten-password.html.twig', [
					'user' => $user,
					'url'  => $this->generateUrl( 'user_reset_password', array( 'token' => $token ), UrlGeneratorInterface::ABSOLUTE_URL ),
			] );

			$mailer->send(
					$this->getParameter( 'plateform' )[ 'from' ],
					$user->getEmail(),
					$mailer->getSubjectFromTitle( $message ),
					$message
			);

			$this->addFlash( 'notice', 'messages.user.password_sent' );

			return $this->redirectToRoute( 'homepage' );
		}

		return $this->render( 'pages/user/password-forgotten.html.twig' );
	}

	/**
	 * @Route("/user/password/reset/{token}", name="user_reset_password")
	 *
	 * @param \Symfony\Component\HttpFoundation\Request                             $request
	 * @param string                                                                $token
	 * @param \Doctrine\Common\Persistence\ObjectManager                            $manager
	 * @param \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface $passwordEncoder
	 *
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function resetPassword (
			Request $request,
			string $token,
			ObjectManager $manager,
			UserPasswordEncoderInterface $passwordEncoder
	) {
		if ( $request->isMethod( 'POST' ) ) {
			/**
			 * @var $user User
			 */
			$user = $manager->getRepository( User::class )->findOneBy( [ 'resetToken' => $token ] );

			if ( $user === NULL ) {
				$this->addFlash( 'error', 'messages.user.password_token_unknown' );

				return $this->redirectToRoute( 'homepage' );
			}

			$user->setPassword( $passwordEncoder->encodePassword( $user, $request->request->get( 'password' ) ) );
			$user->setResetToken( NULL );
			$manager->flush();

			$this->addFlash( 'notice', 'messages.user.password_successful' );

			return $this->redirectToRoute( 'user_login' );
		}
		else {
			return $this->render( 'pages/user/password-reset.html.twig', [ 'token' => $token ] );
		}
	}

	/**
	 * @Route("/user/dashboard", name="user_dashboard")
	 *
	 * @param \Symfony\Component\HttpFoundation\Request  $request
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 *
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function dashboard (
			Request $request,
			ObjectManager $manager
	) {
		$this->denyAccessUnlessGranted( UserVoter::LOGGED );

		/**
		 * @var User $user
		 */
		$user = $this->getUser();

		if ( empty( $user->getName() ) ) {
			return $this->redirectToRoute( 'user_profile_create' );
		}

		$groups = array_map( function ( UsergroupMembership $membership ) {
			return $membership->getUsergroup();
		}, iterator_to_array( $user->getUsergroupMemberships() ) );

		$logEvents = $manager->getRepository( LogEvent::class )
							 ->findForGroups( $groups );

		return $this->render( 'pages/user/dashboard.html.twig', [ 'logEvents' => $logEvents ] );
	}

	protected function profileCreateEdit (
			$template,
			$confirmation,
			Request $request,
			ObjectManager $manager,
			FileManager $fileManager
	) {
		$this->denyAccessUnlessGranted( UserVoter::LOGGED );

		/**
		 * @var User $user
		 */
		$user = $this->getUser();
		$form = $this->createForm( UserProfileType::class, $user );

		$form->handleRequest( $request );

		if ( $form->isSubmitted() && $form->isValid() ) {
			// Site
			$siteName = trim( $form->get( 'siteName' )->getData() );
			if ( !empty( $siteName ) ) {
				$site = $manager->getRepository( Site::class )->findOneBy( [ 'name' => $siteName ] );
				if ( !$site ) {
					$site = new Site();
					$site->setName( $siteName );

					$manager->persist( $site );
				}
				$user->setSite( $site );
			}
			else {
				$user->setSite( NULL );
			}

			// Avatar
			$uploadFile = $form->get( 'avatarfile' )->getData();

			if ( !empty( $uploadFile ) ) {
				/**
				 * @var \App\Service\UserFileManager $userFileManager
				 */
				$userFileManager = $fileManager->getManager( File::USER_FILES );
				$file            = $userFileManager->createFromUploadedFile( $uploadFile, $user );

				$manager->persist( $file );

				$user->setAvatar( $file );
			}
			// --

			$manager->flush();

			$this->addFlash( 'notice', $confirmation );

			return $this->redirectToRoute( 'user_dashboard' );
		}
		else {
			$site = $user->getSite();
			if ( $site ) {
				$form->get( 'siteName' )->setData( $site->getName() );
			}
		}

		return $this->render( $template, [ 'form' => $form->createView(), 'user' => $user ] );
	}

	/**
	 * @Route("/user/profile/create", name="user_profile_create")
	 *
	 * @param \Symfony\Component\HttpFoundation\Request  $request
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 * @param \App\Service\FileManager                   $fileManager
	 *
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function profileCreate (
			Request $request,
			ObjectManager $manager,
			FileManager $fileManager
	) {
		return $this->profileCreateEdit(
				'pages/user/profile-create.html.twig',
				'messages.user.profile_created',
				$request,
				$manager,
				$fileManager
		);
	}

	/**
	 * @Route("/user/profile/edit", name="user_profile_edit")
	 *
	 * @param \Symfony\Component\HttpFoundation\Request  $request
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 * @param \App\Service\FileManager                   $fileManager
	 *
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function profileEdit (
			Request $request,
			ObjectManager $manager,
			FileManager $fileManager
	) {
		return $this->profileCreateEdit(
				'pages/user/profile-edit.html.twig',
				'messages.user.profile_updated',
				$request,
				$manager,
				$fileManager
		);
	}

	/**
	 * @Route("/user/parameters/edit", name="user_parameters_edit")
	 *
	 * @param \Symfony\Component\HttpFoundation\Request                               $request
	 * @param \Doctrine\Common\Persistence\ObjectManager                              $manager
	 * @param \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface   $passwordEncoder
	 * @param \Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface $tokenGenerator
	 * @param \App\Service\EmailSender                                                $mailer
	 *
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function parametersEdit (
			Request $request,
			ObjectManager $manager,
			UserPasswordEncoderInterface $passwordEncoder,
			TokenGeneratorInterface $tokenGenerator,
			EmailSender $mailer
	) {
		$this->denyAccessUnlessGranted( UserVoter::LOGGED );

		/**
		 * @var User $user
		 */
		$user = $this->getUser();

		/**
		 * @var User $emailUserSubmitted
		 */
		$emailUserSubmitted = new User();
		$emailForm          = $this->createForm( UserEmailType::class, $emailUserSubmitted );

		$vars = $request->request->get( 'user_email' );

		if ( !empty( $vars[ 'email_new' ] ) ) {
			$emailForm->handleRequest( $request );
		}

		if ( $emailForm->isSubmitted() && $emailForm->isValid() ) {
			$userRepository = $manager->getRepository( User::class );

			if ( !$passwordEncoder->isPasswordValid( $user, $emailUserSubmitted->getPassword() ) ) {
				$this->addFlash( 'error', 'messages.user.invalid_credentials' );
			}
			else if ( $userRepository->findOneBy( [ 'email' => $vars [ 'email_new' ] ] ) ) {
				$this->addFlash( 'error', 'messages.user.exists' );
			}
			else {
				$token = $tokenGenerator->generateToken();

				try {
					$user->setEmailNew( $vars[ 'email_new' ] );
					$user->setEmailToken( $token );
					$manager->flush();
				} catch ( Exception $e ) {
					$this->addFlash( 'warning', $e->getMessage() );
				}

				// Send Warning

				$message = $this->renderView( 'emails/email-change-warning.html.twig', [
						'user' => $user,
				] );

				$mailer->send(
						$this->getParameter( 'plateform' )[ 'from' ],
						$user->getEmail(),
						$mailer->getSubjectFromTitle( $message ),
						$message
				);

				// Send Confirmation

				$message = $this->renderView( 'emails/email-change-confirm.html.twig', [
						'user' => $user,
						'url'  => $this->generateUrl( 'user_email_confirm', array( 'token' => $token ), UrlGeneratorInterface::ABSOLUTE_URL ),
				] );

				$mailer->send(
						$this->getParameter( 'plateform' )[ 'from' ],
						$vars[ 'email_new' ],
						$mailer->getSubjectFromTitle( $message ),
						$message
				);

				$this->addFlash( 'notice', 'messages.user.email_change_sent' );
			}
		}

		/**
		 * @var User $passwordUserSubmitted
		 */
		$passwordUserSubmitted = new User();
		$passwordForm          = $this->createForm( UserPasswordType::class, $passwordUserSubmitted );

		$vars = $request->request->get( 'user_password' );

		if ( !empty( $vars[ 'password_new' ] ) ) {
			$passwordForm->handleRequest( $request );
		}

		if ( $passwordForm->isSubmitted() && $passwordForm->isValid() ) {
			if ( $vars[ 'password_new' ] !== $vars[ 'password_confirm' ] ) {
				$this->addFlash( 'error', 'messages.user.password_confirm_invalid' );
			}
			else if ( !$passwordEncoder->isPasswordValid( $user, $passwordUserSubmitted->getPassword() ) ) {
				$this->addFlash( 'error', 'messages.user.invalid_credentials' );
			}
			else {
				$user->setPassword( $passwordEncoder->encodePassword( $user, $vars[ 'password_new' ] ) );
				$user->setResetToken( NULL );
				$manager->flush();

				$this->addFlash( 'notice', 'messages.user.password_updated' );

				return $this->redirectToRoute( 'user_dashboard' );
			}
		}

		return $this->render( 'pages/user/parameters-edit.html.twig', [
				'emailForm'    => $emailForm->createView(),
				'passwordForm' => $passwordForm->createView(),
		] );
	}

	/**
	 * @Route("/user/email-change/{token}", name="user_email_confirm")
	 *
	 * @param \Symfony\Component\HttpFoundation\Request  $request
	 * @param string                                     $token
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 *
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function emailChangeConfirm (
			Request $request,
			string $token,
			ObjectManager $manager
	) {
		$this->denyAccessUnlessGranted( UserVoter::LOGGED );

		/**
		 * @var \App\Entity\User $user
		 */
		$user = $this->getUser();

		$userRepository = $manager->getRepository( User::class );

		if ( $userRepository->findOneBy( [ 'email' => $user->getEmailNew() ] ) ) {
			$this->addFlash( 'error', 'messages.user.exists' );
		}
		else if ( ( $token !== $user->getEmailToken() ) || ( empty( $user->getEmailNew() ) ) ) {
			$this->addFlash( 'error', 'messages.user.password_token_unknown' );
		}
		else {
			$user->setEmail( $user->getEmailNew() );
			$user->setEmailToken( NULL );
			$manager->flush();

			$this->addFlash( 'notice', 'messages.user.email_change_successful' );
		}

		return $this->redirectToRoute( 'user_dashboard' );
	}
}
