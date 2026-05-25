<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Test\Subscriber;

use App\Infrastructure\Http\EventSubscriber\DomainExceptionSubscriber;
use DomainException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @internal
 * @coversNothing
 */
final class DomainExceptionSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        $subscriber = DomainExceptionSubscriber::getSubscribedEvents();
        self::assertArrayHasKey(KernelEvents::EXCEPTION, $subscriber);
        self::assertEquals(['onKernelException', 10], $subscriber['kernel.exception']);
    }

    public function testProcessDomainException(): void
    {
        $exception = new DomainException('DomainException');
        $logger = $this->createMock(LoggerInterface::class);
        $subscriber = new DomainExceptionSubscriber($logger);

        $request = Request::create('http://localhost/api/test');
        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new ExceptionEvent(
            $kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $exception
        );

        $logger->expects(self::once())->method('warning')->with(
            self::equalTo('DomainException'),
            self::equalTo([
                'exception' => $exception,
                'url' => 'http://localhost/api/test',
            ])
        );
        $subscriber->onKernelException($event);
        $response = $event->getResponse();

        self::assertNotNull($response);
        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertEquals(JsonResponse::HTTP_BAD_REQUEST, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);

        self::assertEquals(['message' => $exception->getMessage()], $data);
    }

    public function testProcessException(): void
    {
        $exception = new RuntimeException('RuntimeException');
        $logger = $this->createMock(LoggerInterface::class);
        $subscriber = new DomainExceptionSubscriber($logger);

        $request = new Request();
        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new ExceptionEvent(
            $kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $exception
        );
        $logger->expects(self::never())->method('warning');
        $subscriber->onKernelException($event);
        $response = $event->getResponse();

        self::assertNull($response);
    }
}
