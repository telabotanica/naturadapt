<?php

namespace App\Controller;

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
	 * @Route("/user/login", name="app_login")
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function login () {
		return $this->render( 'pages/user/login.html.twig' );
	}

	/**
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
	 * @Route("/user/logout", name="app_logout")
	 */
	public function logout () {
		return $this->redirectToRoute( 'homepage' );
	}

	/**
	 * @Route("/user/register", name="app_register")
	 *
	 * @param \Symfony\Component\HttpFoundation\Request                             $request
	 * @param \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface $passwordEncoder
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @throws \Exception
	 */
	public function register ( Request $request, ObjectManager $manager, UserPasswordEncoderInterface $passwordEncoder ) {
		$error = FALSE;

		if ( $request->isMethod( 'POST' ) ) {
			$userRepository = $manager->getRepository( User::class );

			if ( $userRepository->findOneBy( [ 'email' => $request->request->get( 'email' ) ] ) ) {
				$error = 'errors.user.exists';
			}

			if ( !$error ) {
				$user = new User();
				$user->setCreatedAt( new \DateTime() );
				$user->setEmail( $request->request->get( 'email' ) );
				$user->setPassword( $passwordEncoder->encodePassword( $user, $request->request->get( 'password' ) ) );
				$user->setName( $request->request->get( 'name' ) );
				$manager->persist( $user );

				$manager->flush();

				return $this->redirectToRoute( 'homepage' );
			}
		}

		return $this->render( 'pages/user/register.html.twig', [ 'error' => $error ] );
	}

	/**
	 * @Route("/user/password", name="app_forgotten_password")
	 *
	 * @param \Symfony\Component\HttpFoundation\Request                               $request
	 * @param \Swift_Mailer                                                           $mailer
	 * @param \Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface $tokenGenerator
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function forgottenPassword ( Request $request, ObjectManager $manager, \Swift_Mailer $mailer, TokenGeneratorInterface $tokenGenerator ) {
		if ( $request->isMethod( 'POST' ) ) {
			$email = $request->request->get( 'email' );
			/**
			 * @var $user User
			 */
			$user = $manager->getRepository( User::class )->findOneByEmail( $email );

			if ( $user === NULL ) {
				$this->addFlash( 'danger', 'Unknown email' );

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

			$url = $this->generateUrl( 'app_reset_password', array ( 'token' => $token ), UrlGeneratorInterface::ABSOLUTE_URL );

			$message = ( new \Swift_Message( 'Forgot Password' ) )
					->setFrom( 'noreply@naturadapt.org' )
					->setTo( $user->getEmail() )
					->setBody( "blablabla voici le token pour reseter votre mot de passe : " . $url, 'text/html' );

			$mailer->send( $message );

			$this->addFlash( 'notice', 'Email sent' );

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
	public function resetPassword ( Request $request, string $token, UserPasswordEncoderInterface $passwordEncoder ) {
		if ( $request->isMethod( 'POST' ) ) {
			$em = $this->getDoctrine()->getManager();

			/**
			 * @var $user User
			 */
			$user = $em->getRepository( User::class )->findOneByResetToken( $token );

			if ( $user === NULL ) {
				$this->addFlash( 'danger', 'Unkown token' );

				return $this->redirectToRoute( 'homepage' );
			}

			$user->setResetToken( NULL );
			$user->setPassword( $passwordEncoder->encodePassword( $user, $request->request->get( 'password' ) ) );
			$em->flush();

			$this->addFlash( 'notice', 'Password updated' );

			return $this->redirectToRoute( 'homepage' );
		}
		else {
			return $this->render( 'pages/user/reset-password.html.twig', [ 'token' => $token ] );
		}
	}
}
