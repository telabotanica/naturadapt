<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * Use locale stored in session if not explicilty set by routing
 * Stores the locale of the user in the session after the login.
 */
class UserLocaleSubscriber implements EventSubscriberInterface {
	private $defaultLocale;
	private $session;

	public function __construct ( SessionInterface $session, $defaultLocale = 'fr' ) {
		$this->session       = $session;
		$this->defaultLocale = $defaultLocale;
	}

	public function onKernelRequest ( GetResponseEvent $event ) {
		$request = $event->getRequest ();

		if ( !$request->hasPreviousSession () ) {
			return;
		}

		// try to see if the locale has been set as a _locale routing parameter
		if ( $locale = $request->attributes->get ( '_locale' ) ) {
			$request->getSession ()->set ( '_locale', $locale );
		}
		else {
			// if no explicit locale has been set on this request, use one from the session
			$request->setLocale ( $request->getSession ()->get ( '_locale', $this->defaultLocale ) );
		}
	}

	public function onInteractiveLogin ( InteractiveLoginEvent $event ) {
		$user = $event->getAuthenticationToken ()->getUser ();

		if ( NULL !== $user->getLocale () ) {
			$this->session->set ( '_locale', $user->getLocale () );
		}
	}

	public static function getSubscribedEvents () {
		return [
			// must be registered before (i.e. with a higher priority than) the default Locale listener
			KernelEvents::REQUEST             => [ [ 'onKernelRequest', 20 ] ],
			SecurityEvents::INTERACTIVE_LOGIN => 'onInteractiveLogin',
		];
	}
}
