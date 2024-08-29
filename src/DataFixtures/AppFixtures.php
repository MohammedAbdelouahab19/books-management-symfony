<?php
declare(strict_types=1);

namespace App\DataFixtures;

use App\Factory\AuthorFactory;
use App\Factory\BookFactory;
use App\Factory\CategoryFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    private const int COUNT_CATEGORIES = 10;
    private const int COUNT_AUTHORS = 10;
    private const int COUNT_BOOKS = 10;

    public function load(ObjectManager $manager): void
    {
        CategoryFactory::createMany(self::COUNT_CATEGORIES);
        AuthorFactory::createMany(self::COUNT_AUTHORS);
        BookFactory::createMany(self::COUNT_BOOKS);

        $manager->flush();
    }
}
