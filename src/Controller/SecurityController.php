<?php

namespace App\Controller;

use App\Service\EmailSender;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\User;

class SecurityController extends AbstractController {
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

		return $this->render( 'components/user/login.html.twig', [
				'error'         => $error,
				'last_username' => $lastUsername,
		] );
	}

	/**
	 * @Route("/user/login", name="app_login")
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function loginPage () {
		return $this->render( 'pages/user/login.html.twig' );
	}

	/**
	 * @Route("/user/logout", name="app_logout")
	 */
	public function logout () {
		return $this->redirectToRoute( 'homepage' );
	}

	/**
	 * @Route("/user/register", name="app_register")
	 *
	 * @param \Symfony\Component\HttpFoundation\Request                               $request
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
		$error = FALSE;

		if ( $request->isMethod( 'POST' ) ) {
			$userRepository = $manager->getRepository( User::class );

			if ( $userRepository->findOneBy( [ 'email' => $request->request->get( 'email' ) ] ) ) {
				$error = 'user.exists';
			}

			if ( !$error ) {
				$user = new User();
				$user->setCreatedAt( new \DateTime() );
				$user->setEmail( $request->request->get( 'email' ) );
				$user->setPassword( $passwordEncoder->encodePassword( $user, $request->request->get( 'password' ) ) );
				$user->setName( $request->request->get( 'name' ) );
				$user->setRoles( [ User::ROLE_USER ] );
				$user->setStatus( User::STATUS_PENDING );

				$token = $tokenGenerator->generateToken();
				$user->setResetToken( $token );

				$manager->persist( $user );
				$manager->flush();

				$message = $this->renderView( 'emails/register-activation.html.twig', [
						'url' => $this->generateUrl( 'app_activate', array ( 'token' => $token ), UrlGeneratorInterface::ABSOLUTE_URL ),
				] );

				$mailer->send(
						$this->getParameter( 'plateform' )[ 'from' ],
						$user->getEmail(),
						$mailer->getSubjectFromTitle( $message ),
						$message
				);

				$this->addFlash( 'notice', 'user.activation_sent' );

				return $this->redirectToRoute( 'homepage' );
			}
		}

		return $this->render( 'pages/user/register.html.twig', [ 'error' => $error ] );
	}

	/**
	 * @Route("/user/activate/{token}", name="app_activate")
	 *
	 * @param \Symfony\Component\HttpFoundation\Request                             $request
	 * @param string                                                                $token
	 * @param \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface $passwordEncoder
	 *
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function activate ( Request $request, string $token, ObjectManager $manager ) {
		/**
		 * @var $user User
		 */
		$user = $manager->getRepository( User::class )->findOneByResetToken( $token );

		if ( $user === NULL ) {
			$this->addFlash( 'danger', 'user.activation_token_unknown' );

			return $this->redirectToRoute( 'homepage' );
		}

		if ( $user->getStatus() === User::STATUS_ACTIVE ) {
			$this->addFlash( 'danger', 'user.activation_already_active' );

			return $this->redirectToRoute( 'homepage' );
		}

		if ( $user->getStatus() !== User::STATUS_PENDING ) {
			$this->addFlash( 'danger', 'user.activation_impossible' );

			return $this->redirectToRoute( 'homepage' );
		}

		$user->setStatus( User::STATUS_ACTIVE );
		$user->setResetToken( NULL );

		$manager->flush();

		$this->addFlash( 'notice', 'user.activation_successful' );

		return $this->redirectToRoute( 'app_login' );
	}

	/**
	 * @Route("/user/password", name="app_forgotten_password")
	 *
	 * @param \Symfony\Component\HttpFoundation\Request                               $request
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
			$user = $manager->getRepository( User::class )->findOneByEmail( $email );

			if ( $user === NULL ) {
				$this->addFlash( 'danger', 'user.unknown' );

				return $this->redirectToRoute( 'homepage' );
			}

			if ( $user->getStatus() !== User::STATUS_ACTIVE ) {
				$this->addFlash( 'danger', 'user.inactive' );

				return $this->redirectToRoute( 'homepage' );
			}

			$token = $tokenGenerator->generateToken();

			try {
				$user->setResetToken( $token );
				$manager->flush();
			} catch ( \Exception $e ) {
				$this->addFlash( 'warning', $e->getMessage() );

				return $this->redirectToRoute( 'homepage' );
			}

			$message = $this->renderView( 'emails/forgotten-password.html.twig', [
					'url' => $this->generateUrl( 'app_reset_password', array ( 'token' => $token ), UrlGeneratorInterface::ABSOLUTE_URL ),
			] );

			$mailer->send(
					$this->getParameter( 'plateform' )[ 'from' ],
					$user->getEmail(),
					$mailer->getSubjectFromTitle( $message ),
					$message
			);

			$this->addFlash( 'notice', 'user.password_sent' );

			return $this->redirectToRoute( 'homepage' );
		}

		return $this->render( 'pages/user/forgotten-password.html.twig' );
	}

	/**
	 * @Route("/user/password/reset/{token}", name="app_reset_password")
	 *
	 * @param \Symfony\Component\HttpFoundation\Request                             $request
	 * @param string                                                                $token
	 * @param \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface $passwordEncoder
	 *
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function resetPassword ( Request $request, string $token, ObjectManager $manager, UserPasswordEncoderInterface $passwordEncoder ) {
		if ( $request->isMethod( 'POST' ) ) {
			/**
			 * @var $user User
			 */
			$user = $manager->getRepository( User::class )->findOneByResetToken( $token );

			if ( $user === NULL ) {
				$this->addFlash( 'danger', 'user.password_token_unknown' );

				return $this->redirectToRoute( 'homepage' );
			}

			$user->setPassword( $passwordEncoder->encodePassword( $user, $request->request->get( 'password' ) ) );
			$user->setResetToken( NULL );
			$manager->flush();

			$this->addFlash( 'notice', 'user.password_successful' );

			return $this->redirectToRoute( 'app_login' );
		}
		else {
			return $this->render( 'pages/user/reset-password.html.twig', [ 'token' => $token ] );
		}
	}
}
