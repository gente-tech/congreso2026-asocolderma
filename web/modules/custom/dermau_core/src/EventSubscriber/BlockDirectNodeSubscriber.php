<?php

namespace Drupal\dermau_core\EventSubscriber;

use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Bloquea el acceso público directo mediante /node/{nid}.
 */
class BlockDirectNodeSubscriber implements EventSubscriberInterface
{

	/**
	 * Usuario actual.
	 */
	protected AccountProxyInterface $currentUser;

	/**
	 * Constructor.
	 */
	public function __construct(AccountProxyInterface $current_user)
	{
		$this->currentUser = $current_user;
	}

	/**
	 * Intercepta las solicitudes entrantes.
	 */
	public function onKernelRequest(RequestEvent $event): void
	{
		if (!$event->isMainRequest()) {
			return;
		}

		// Los usuarios administrativos pueden acceder normalmente.
		if (
			$this->currentUser->hasPermission('administer nodes')
			|| $this->currentUser->hasPermission('bypass node access')
		) {
			return;
		}

		$request = $event->getRequest();
		$path = $request->getPathInfo();

		// Bloquea únicamente:
		// /node/123
		// /node/123/
		//
		// No bloquea:
		// /node/123/edit
		// /node/123/delete
		// ni aliases como /agenda.
		if (preg_match('#^/node/\d+/?$#', $path)) {
			throw new NotFoundHttpException();
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			KernelEvents::REQUEST => ['onKernelRequest', 100],
		];
	}
}
