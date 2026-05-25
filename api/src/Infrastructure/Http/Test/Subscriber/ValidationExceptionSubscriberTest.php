<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Test\Subscriber;

use App\Infrastructure\Http\EventSubscriber\ValidationExceptionSubscriber;
use App\Infrastructure\Http\Validator\ValidationException;
use DomainException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * @internal
 * @coversNothing
 */
final class ValidationExceptionSubscriberTest extends TestCase
{
    public function testSubscribedEvents(): void
    {
        $subscriber = ValidationExceptionSubscriber::getSubscribedEvents();
        self::assertArrayHasKey(KernelEvents::EXCEPTION, $subscriber);

        self::assertEquals(['onKernelException', 10], $subscriber[KernelEvents::EXCEPTION]);
    }

    public function testProcessException(): void
    {
        $exception = new DomainException('DomainException');
        $subscriber = new ValidationExceptionSubscriber();
        $request = new Request();

        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new ExceptionEvent(
            $kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $exception
        );

        $subscriber->onKernelException($event);
        $response = $event->getResponse();

        self::assertNull($response);
    }

    public function testProcessValidationException(): void
    {
        $violations = new ConstraintViolationList([
            new ConstraintViolation('Incorrect Email', null, [], null, 'email', 'not-email'),
            new ConstraintViolation('Empty Password', null, [], null, 'password', ''),
        ]);

        $exception = new ValidationException($violations);

        $subscriber = new ValidationExceptionSubscriber();

        $request = new Request();
        $kernel = $this->createMock(HttpKernelInterface::class);

        $event = new ExceptionEvent(
            $kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $exception
        );

        $subscriber->onKernelException($event);
        $response = $event->getResponse();

        self::assertNotNull($response);

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertEquals(JsonResponse::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        self::assertJson($body = (string)$response->getContent());

        $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        self::assertEquals(['errors' => [
            'email' => 'Incorrect Email',
            'password' => 'Empty Password',
        ]], $data);
    }
}
