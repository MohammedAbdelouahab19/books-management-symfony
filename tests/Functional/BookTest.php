<?php
declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Book;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;

class BookTest extends AbstractApiTest
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testCreateBook(): void
    {
        static::createClient()->request('POST', '/api/books', [
            'json' => [
                'title' => 'Harry Potter and the Philosopher\'s Stone',
                'publicationDate' => '1800-06-26',
                'category' => [
                    '/api/categories/1',
                    '/api/categories/2',
                ],
                'author' => '/api/authors/1',
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertMatchesResourceItemJsonSchema(Book::class);
        $this->assertJsonContains([
            '@context' => '/api/contexts/Book',
            '@type' => 'Book',
            'title' => 'Harry Potter and the Philosopher\'s Stone',
            'publicationDate' => '1800-06-26T00:00:00+00:00',
        ]);
    }

    public function testCreateInvalidBook(): void
    {
        static::createClient()->request('POST', '/api/books', [
            'json' => [
                'title' => '',
                'publicationDate' => null,
                'category' => [
                    '/api/categories/1',
                    '/api/categories/2',
                ],
                'author' => '/api/authors/1',
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

    public function testRetrieveBookById(): void
    {
        $iri = $this->findIriBy(Book::class, ['id' => 11]);
        static::createClient()->request('GET', $iri);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@context' => '/api/contexts/Book',
            '@id' => '/api/books/11',
            '@type' => 'Book',
            'title' => 'Harry Potter and the Philosopher\'s Stone',
            'publicationDate' => '1800-06-26T00:00:00+00:00',
        ]);
    }

    public function testUpdateBook(): void
    {
        $iri = $this->findIriBy(Book::class, ['id' => 1]);

        static::createClient()->request('GET', $iri);
        $this->assertResponseIsSuccessful();

        static::createClient()->request('PUT', $iri, [
            'json' => [
                'title' => 'Harry Potter and the Chamber of Secrets',
                'publicationDate' => '1801-07-02',
                'category' => [
                    '/api/categories/3',
                    '/api/categories/4',
                    '/api/categories/7',
                ],
                'author' => '/api/authors/5',
            ]
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@context' => '/api/contexts/Book',
            '@type' => 'Book',
            '@id' => $iri,
            'title' => 'Harry Potter and the Chamber of Secrets',
            'publicationDate' => '1801-07-02T00:00:00+00:00',
        ]);
    }

    public function testFilterBooksByName(): void
    {
        $response = static::createClient()->request('GET', '/api/books?publicationDate[before]=1802-08-30');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertCount(2, $response->toArray()['hydra:member']);
    }

    public function testDeleteBook(): void
    {
        $iri = $this->findIriBy(Book::class, ['id' => 11]);

        static::createClient()->request('DELETE', $iri);

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        $this->assertNull($this->findIriBy(Book::class, ['id' => 11]));
    }
}
