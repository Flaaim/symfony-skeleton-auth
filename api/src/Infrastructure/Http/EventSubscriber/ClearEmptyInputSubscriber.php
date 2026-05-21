<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class ClearEmptyInputSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 100],
        ];
    }
    public static function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if($request->request->count() > 0){
            $cleanedData = self::filterStrings($request->request->all());
            $request->request->replace($cleanedData);
        }

        if ($request->files->count() > 0) {
            $cleanedFiles = self::filterFiles($request->files->all());
            $request->files->replace($cleanedFiles);
        }

    }

    private static function filterStrings($items)
    {
        if (!is_array($items)) {
            return $items;
        }

        $result = [];
        foreach ($items as $key => $item) {
            if (is_string($item)) {
                $result[$key] = trim($item);
            } else {
                $result[$key] = self::filterStrings($item);
            }
        }

        return $result;
    }

    private static function filterFiles(array $items): array
    {
        $result = [];

        foreach ($items as $key => $item) {
            if ($item instanceof UploadedFile) {
                if ($item->getError() !== \UPLOAD_ERR_NO_FILE) {
                    $result[$key] = $item;
                }
            } elseif (is_array($item)) {
                $result[$key] = self::filterFiles($item);
            }
        }

        return $result;
    }
}
