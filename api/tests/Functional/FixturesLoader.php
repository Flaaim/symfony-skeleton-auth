<?php

declare(strict_types=1);

namespace Tests\Functional;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class FixturesLoader
{
    public function __construct(
        private readonly ContainerInterface $container,
    ) {}
    public function loadFixtures(array $fixtures): void
    {
        $loader = new Loader();
        foreach ($fixtures as $className) {
            /** @var AbstractFixture $fixture */
            if ($this->container->has($className)) {
                $fixture = $this->container->get($className);
            } else {
                $fixture = new $className();
            }
            $loader->addFixture($fixture);
        }
        /** @var EntityManagerInterface $em */
        $em = $this->container->get(EntityManagerInterface::class);
        $executor = new ORMExecutor($em, new ORMPurger($em));
        $executor->execute($loader->getFixtures());

        $em->clear();
    }

}
