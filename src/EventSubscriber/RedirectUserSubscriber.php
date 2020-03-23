<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RedirectUserSubscriber implements EventSubscriberInterface {
	/**
	 * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface
	 */
	private $tokenStorage;

	/**
	 * @var \Symfony\Component\Routing\RouterInterface
	 */
	private $router;

	public function __construct (
			TokenStorageInterface $tokenStorage,
			RouterInterface $router
	) {
		$this->tokenStorage = $tokenStorage;
		$this->router       = $router;
	}

	public static function getSubscribedEvents () {
		return [
				KernelEvents::REQUEST => [ [ 'onKernelRequest' ] ],
		];
	}

	public function onKernelRequest ( GetResponseEvent $event ) {
		if ( !$event->isMasterRequest() ) {
			return;
		}

		if ( !( $token = $this->tokenStorage->getToken() ) ) {
			return;
		}

		if ( !$token->isAuthenticated() ) {
			return;
		}

		/**
		 * @var \App\Entity\User $user
		 */
		if ( !$user = $token->getUser() ) {
			return;
		}

		if ( method_exists( $user, 'getName' ) && empty( $user->getName() ) ) {
			$request = $event->getRequest();
			$route   = $request->attributes->get( '_route' );

			dump( $route );

			switch ( TRUE ) {
				case ( $route === 'homepage' ):
				case ( $route === 'groups_index' ):
				case ( substr( $route, 0, 6 ) === 'group_' ):
				case ( $route === 'members' ):
				case ( $route === 'member' ):
				case ( $route === 'user_dashboard' ):
					$request->getSession()->getFlashBag()->add( 'notice', 'messages.user.profile_required' );
					$event->setResponse( new RedirectResponse( $this->router->generate( 'user_profile_create' ) ) );
					break;
			}
		}
	}
}
