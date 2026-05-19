<?php

declare(strict_types=1);

namespace App\Frontend\Test\Unit;

use App\Frontend\FrontendUrlTwigExtension;
use App\Frontend\FrontendUrlGeneratorInterface;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class FrontendUrlTwigExtensionTest extends TestCase
{
    public function testSuccess(): void
    {
        $frontend = $this->createMock(FrontendUrlGeneratorInterface::class);
        $frontend->expects($this->once())->method('generate')->with(
            $this->equalTo('path'),
            $this->equalTo(['a' => 1, 'b' => 2])
        )->willReturn('http://test/path?a=1&b=2');

        $twig = new Environment(new ArrayLoader([
            'page.html.twig' => '<p>{{ frontend_url(\'path\', {\'a\': 1, \'b\': 2}) }}</p>',
        ]));

        $twig->addExtension(new FrontendUrlTwigExtension($frontend));

        self::assertEquals('<p>http://test/path?a=1&amp;b=2</p>', $twig->render('page.html.twig'));
    }
}
