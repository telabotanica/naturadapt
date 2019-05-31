<?php

namespace App\Controller;

use App\Entity\File;
use App\Entity\Site;
use App\Entity\User;
use App\Form\UserProfileType;
use App\Security\UserVoter;
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

		return $this->render( 'components/user/login.html.twig', [
				'last_username' => $lastUsername,
		] );
	}

	/**
	 * @Route("/user/login", name="user_login")
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function loginPage () {
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
			$manager->flush();

			$message = $this->renderView( 'emails/register-activation.html.twig', [
					'url' => $this->generateUrl( 'user_activate', array ( 'token' => $token ), UrlGeneratorInterface::ABSOLUTE_URL ),
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
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function activate (
			Request $request,
			string $token,
			ObjectManager $manager,
			SessionInterface $session,
			TokenStorageInterface $tokenStorage,
			EventDispatcherInterface $eventDispatcher
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
					'url' => $this->generateUrl( 'user_reset_password', array ( 'token' => $token ), UrlGeneratorInterface::ABSOLUTE_URL ),
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

		return $this->render( 'pages/user/forgotten-password.html.twig' );
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
			return $this->render( 'pages/user/reset-password.html.twig', [ 'token' => $token ] );
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

		return $this->render( 'pages/user/dashboard.html.twig' );
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

		return $this->render( $template, [ 'form' => $form->createView() ] );
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
}
