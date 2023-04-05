<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator {
	use TargetPathTrait;

	private $entityManager;
	private $router;
	private $csrfTokenManager;
	private $passwordEncoder;

	public function __construct ( EntityManagerInterface $entityManager, RouterInterface $router, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordEncoderInterface $passwordEncoder ) {
		$this->entityManager    = $entityManager;
		$this->router           = $router;
		$this->csrfTokenManager = $csrfTokenManager;
		$this->passwordEncoder  = $passwordEncoder;
	}

	public function supports ( Request $request ) {
		return ( $request->attributes->get( '_route' ) === 'user_login' )
			   && $request->isMethod( 'POST' )
			   && $request->request->has( 'email' )
			   && $request->request->has( 'password' )
			   && $request->request->has( '_csrf_token' );
	}

	public function getCredentials ( Request $request ) {
		$credentials = [
				'email'      => $request->request->get( 'email' ),
				'password'   => $request->request->get( 'password' ),
				'csrf_token' => $request->request->get( '_csrf_token' ),
		];
		$request->getSession()->set(
				Security::LAST_USERNAME,
				$credentials[ 'email' ]
		);

		return $credentials;
	}

	public function getUser ( $credentials, UserProviderInterface $userProvider ) {
		$token = new CsrfToken( 'authenticate', $credentials[ 'csrf_token' ] );
		if ( !$this->csrfTokenManager->isTokenValid( $token ) ) {
			throw new InvalidCsrfTokenException();
		}

		/**
		 * @var \App\Entity\User $user
		 */
		$user = $this->entityManager->getRepository( User::class )->findOneBy( [ 'email' => $credentials[ 'email' ] ] );

		if ( $user === NULL ) {
			throw new CustomUserMessageAuthenticationException( 'messages.user.unknown' );
		}

		return $user;
	}

	public function checkCredentials ( $credentials, UserInterface $user ) {
		return $this->passwordEncoder->isPasswordValid( $user, $credentials[ 'password' ] );
	}

	public function onAuthenticationSuccess ( Request $request, TokenInterface $token, $providerKey ) {
		
		$user = $token->getUser();
		if (!$user instanceof UserInterface) {
			throw new \Exception('Invalid user object');
		}
	
		if (!$user->getHasBeenNotifiedOfNewAdaptativeApproach()) {
			// Add a flash message to notify the user
			$request->getSession()->getFlashBag()->add('warning', 'Veuillez remplir le nouveau champs "Démarche adaptative" sur votre profil.');
			$user->setHasBeenNotifiedOfNewAdaptativeApproach(true);

			// Rediriger vers la page de notification pour demander à l'utilisateur de mettre à jour sa variable hasBeenNotifiedOfNewAdaptativeApproach
			return new RedirectResponse($this->router->generate('user_profile_create'));
		}
		
		if ( $targetPath = $this->getTargetPath( $request->getSession(), $providerKey ) ) {
			return new RedirectResponse( $targetPath );
		}

		return new RedirectResponse( $this->router->generate( 'user_dashboard' ) );
	}

	protected function getLoginUrl () {
		return $this->router->generate( 'user_login' );
	}
}
