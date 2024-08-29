<?php
declare(strict_types=1);

namespace App\Tests\Functional;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

abstract class AbstractApiTest extends ApiTestCase
{
//    use RefreshDataBaseTrait;

    public function setUp(): void
    {
        self::bootKernel();
    }
}
