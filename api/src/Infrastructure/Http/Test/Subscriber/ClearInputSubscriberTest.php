<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Test\Subscriber;

use App\Infrastructure\Http\EventSubscriber\ClearEmptyInputSubscriber;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final class ClearInputSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        $subscriber = ClearEmptyInputSubscriber::getSubscribedEvents();
        self::assertArrayHasKey(KernelEvents::REQUEST, $subscriber);
        self::assertEquals(['onKernelRequest', 20], $subscriber['kernel.request']);


    }
    public function testParsedBody(): void
    {
        $subscriber = new ClearEmptyInputSubscriber();
        $request = new Request();

        $request->request->replace([
            'null' => null,
            'space' => ' ',
            'string' => 'String ',
            'nested' => [
                'null' => null,
                'space' => ' ',
                'name' => ' Name',
            ]
        ]);

        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new RequestEvent(
            $kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $subscriber->onKernelRequest($event);

        self::assertEquals([
            'null' => null,
            'space' => '',
            'string' => 'String',
            'nested' => [
                'null' => null,
                'space' => '',
                'name' => 'Name',
            ]
        ], $request->request->all());
    }

    public function testUploadedFiles(): void
    {
        $subscriber = new ClearEmptyInputSubscriber();
        $request = new Request();
        $root = vfsStream::setup('uploads');

        $file = vfsStream::newFile('document.txt')
            ->at($root)
            ->setContent('test');
        $realFile = new UploadedFile($file->url(), 'original_name.txt', 'text/plain', UPLOAD_ERR_OK, true);

        $noFile = new UploadedFile($file->url(), 'no_file.txt', 'text/plain', UPLOAD_ERR_NO_FILE, true);

        $request->files->replace([
            'realFile' => $realFile,
            'noFile' => $noFile,
            'arrayFiles' => [$realFile, $noFile],
            'emptyArray' => [$noFile, $noFile],
        ]);


        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new RequestEvent(
            $kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $subscriber->onKernelRequest($event);

        self::assertEquals([
            'realFile' => $realFile,
            'arrayFiles' => [$realFile],
            'emptyArray' => [],
        ], $request->files->all());
    }
}
