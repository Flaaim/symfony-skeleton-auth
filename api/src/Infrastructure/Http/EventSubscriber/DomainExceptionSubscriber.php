<?php

declare(strict_types=1);

namespace Infrastructure\Http\EventSubscriber;

use DomainException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class DomainExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {}
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (!$exception instanceof DomainException) {
            return;
        }

        $this->logger->warning($exception->getMessage(), [
            'exception' => $exception,
            'url' => $event->getRequest()->getUri(),
        ]);

        $response = new JsonResponse([
            'message' => $exception->getMessage(),
        ], JsonResponse::HTTP_CONFLICT);

        $event->setResponse($response);
    }
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 10],
        ];
    }
}
