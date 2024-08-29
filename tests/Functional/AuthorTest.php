<?php
declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Author;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;

class AuthorTest extends AbstractApiTest
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testCreateAuthor(): void
    {
        static::createClient()->request('POST', '/api/authors', [
            'json' => [
                'name' => 'J.K. Rowling',
                'birthDay' => '1965-07-31',
                'biography' => 'British author, best known for the Harry Potter series.',
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertMatchesResourceItemJsonSchema(Author::class);
        $this->assertJsonContains([
            '@context' => '/api/contexts/Author',
            '@type' => 'Author',
            'name' => 'J.K. Rowling',
            'birthDay' => '1965-07-31T00:00:00+00:00',
            'biography' => 'British author, best known for the Harry Potter series.',
        ]);
    }

    public function testCreateInvalidBook(): void
    {
        static::createClient()->request('POST', '/api/authors', [
            'json' => [
                'name' => '',
                'birthDay' => null,
                'biography' => null,
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        self::assertJsonContains([
            '@context'          => '/api/contexts/ConstraintViolationList',
            '@type'             => 'ConstraintViolationList',
            'hydra:title'       => 'An error occurred',
        ]);
    }

    public function testRetrieveAuthorById(): void
    {
        $iri = $this->findIriBy(Author::class, ['id' => 11]);
        static::createClient()->request('GET', $iri);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@context' => '/api/contexts/Author',
            '@id' => '/api/authors/11',
            '@type' => 'Author',
            'name' => 'J.K. Rowling',
            'birthDay' => '1965-07-31T00:00:00+00:00',
            'biography' => 'British author, best known for the Harry Potter series.',
        ]);
    }

    public function testUpdateAuthor(): void
    {
        $iri = $this->findIriBy(Author::class, ['id' => 1]);

        static::createClient()->request('GET', $iri);
        $this->assertResponseIsSuccessful();

        static::createClient()->request('PUT', $iri, [
            'json' => [
                'name' => 'J.K. Rowling',
                'birthDay' => '1965-07-31',
            ]
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@context' => '/api/contexts/Author',
            '@type' => 'Author',
            '@id' => $iri,
            'name' => 'J.K. Rowling',
        ]);
    }

    public function testFilterAuthorsByName(): void
    {
        $response = static::createClient()->request('GET', '/api/authors?name=J.K. Rowling');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

//        $this->assertCount(2, $response->toArray()['hydra:member']);
    }

    public function testDeleteAuthor(): void
    {
        $iri = $this->findIriBy(Author::class, ['id' => 11]);

        static::createClient()->request('DELETE', $iri);

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        $this->assertNull($this->findIriBy(Author::class, ['id' => 11]));
    }
}
