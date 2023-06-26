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
use Symfony\Contracts\Translation\TranslatorInterface;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator {
	use TargetPathTrait;

	private $entityManager;
	private $router;
	private $csrfTokenManager;
	private $passwordEncoder;
	private $translator;

	public function __construct ( EntityManagerInterface $entityManager, RouterInterface $router, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordEncoderInterface $passwordEncoder, TranslatorInterface $translator ) {
		$this->entityManager    = $entityManager;
		$this->router           = $router;
		$this->csrfTokenManager = $csrfTokenManager;
		$this->passwordEncoder  = $passwordEncoder;
		$this->translator       = $translator;
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
			// TODO: Code à enlever une fois que les utilisateurs auront mis à jour leur profil
			$adaptativeFormMessage = $this->translator->trans('messages.user.adaptative_approach_required', ['%link%' => "#user_profile_hasAdaptativeApproach"]);
			$request->getSession()->getFlashBag()->add('warning', $adaptativeFormMessage);
			$this->entityManager->flush();

			// Rediriger vers la page de notification pour demander à l'utilisateur de mettre à jour sa variable hasBeenNotifiedOfNewAdaptativeApproach
			return new RedirectResponse($this->router->generate('user_profile_edit'));
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
