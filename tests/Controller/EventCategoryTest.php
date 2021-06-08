<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Tests\Controller;

use App\DataFixtures\EventCategoryFixtures;
use App\Repository\EventCategoryRepository;
use Nines\UserBundle\DataFixtures\UserFixtures;
use Nines\UtilBundle\Tests\ControllerBaseCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 * @coversNothing
 */
class EventCategoryTest extends ControllerBaseCase {
    // Change this to HTTP_OK when the site is public.
    private const ANON_RESPONSE_CODE = Response::HTTP_FOUND;

    private const TYPEAHEAD_QUERY = 'label';

    protected function fixtures() : array {
        return [
            EventCategoryFixtures::class,
            UserFixtures::class,
        ];
    }

    /**
     * @group anon
     * @group index
     */
    public function testAnonIndex() : void {
        $crawler = $this->client->request('GET', '/event_category/');
        static::assertSame(self::ANON_RESPONSE_CODE, $this->client->getResponse()->getStatusCode());
        static::assertSame(0, $crawler->selectLink('New')->count());
    }

    /**
     * @group index
     * @group user
     */
    public function testUserIndex() : void {
        $this->login('user.user');
        $crawler = $this->client->request('GET', '/event_category/');
        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        static::assertSame(0, $crawler->selectLink('New')->count());
    }

    /**
     * @group admin
     * @group index
     */
    public function testAdminIndex() : void {
        $this->login('user.admin');
        $crawler = $this->client->request('GET', '/event_category/');
        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        static::assertSame(1, $crawler->selectLink('New')->count());
    }

    /**
     * @group anon
     * @group show
     */
    public function testAnonShow() : void {
        $crawler = $this->client->request('GET', '/event_category/1');
        static::assertSame(self::ANON_RESPONSE_CODE, $this->client->getResponse()->getStatusCode());
        static::assertSame(0, $crawler->selectLink('Edit')->count());
    }

    /**
     * @group show
     * @group user
     */
    public function testUserShow() : void {
        $this->login('user.user');
        $crawler = $this->client->request('GET', '/event_category/1');
        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        static::assertSame(0, $crawler->selectLink('Edit')->count());
    }

    /**
     * @group admin
     * @group show
     */
    public function testAdminShow() : void {
        $this->login('user.admin');
        $crawler = $this->client->request('GET', '/event_category/1');
        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        static::assertSame(1, $crawler->selectLink('Edit')->count());
    }

    /**
     * @group anon
     * @group typeahead
     */
    public function testAnonTypeahead() : void {
        $this->client->request('GET', '/event_category/typeahead?q=' . self::TYPEAHEAD_QUERY);
        $response = $this->client->getResponse();
        static::assertSame(self::ANON_RESPONSE_CODE, $this->client->getResponse()->getStatusCode());
        if (self::ANON_RESPONSE_CODE === Response::HTTP_FOUND) {
            // If authentication is required stop here.
            return;
        }
        static::assertSame('application/json', $response->headers->get('content-type'));
        $json = json_decode($response->getContent());
        static::assertCount(4, $json);
    }

    /**
     * @group typeahead
     * @group user
     */
    public function testUserTypeahead() : void {
        $this->login('user.user');
        $this->client->request('GET', '/event_category/typeahead?q=' . self::TYPEAHEAD_QUERY);
        $response = $this->client->getResponse();
        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        static::assertSame('application/json', $response->headers->get('content-type'));
        $json = json_decode($response->getContent());
        static::assertCount(4, $json);
    }

    /**
     * @group admin
     * @group typeahead
     */
    public function testAdminTypeahead() : void {
        $this->login('user.admin');
        $this->client->request('GET', '/event_category/typeahead?q=' . self::TYPEAHEAD_QUERY);
        $response = $this->client->getResponse();
        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        static::assertSame('application/json', $response->headers->get('content-type'));
        $json = json_decode($response->getContent());
        static::assertCount(4, $json);
    }

    public function testAnonSearch() : void {
        $repo = $this->createMock(EventCategoryRepository::class);
        $repo->method('searchQuery')->willReturn([$this->getReference('eventcategory.1')]);
        $this->client->disableReboot();
        $this->client->getContainer()->set('test.' . EventCategoryRepository::class, $repo);

        $crawler = $this->client->request('GET', '/event_category/search');
        static::assertSame(self::ANON_RESPONSE_CODE, $this->client->getResponse()->getStatusCode());
        if (self::ANON_RESPONSE_CODE === Response::HTTP_FOUND) {
            // If authentication is required stop here.
            return;
        }

        $form = $crawler->selectButton('btn-search')->form([
            'q' => 'eventCategory',
        ]);

        $responseCrawler = $this->client->submit($form);
        static::assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function testUserSearch() : void {
        $repo = $this->createMock(EventCategoryRepository::class);
        $repo->method('searchQuery')->willReturn([$this->getReference('eventcategory.1')]);
        $this->client->disableReboot();
        $this->client->getContainer()->set('test.' . EventCategoryRepository::class, $repo);

        $this->login('user.user');
        $crawler = $this->client->request('GET', '/event_category/search');
        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('btn-search')->form([
            'q' => 'eventCategory',
        ]);

        $responseCrawler = $this->client->submit($form);
        static::assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminSearch() : void {
        $repo = $this->createMock(EventCategoryRepository::class);
        $repo->method('searchQuery')->willReturn([$this->getReference('eventcategory.1')]);
        $this->client->disableReboot();
        $this->client->getContainer()->set('test.' . EventCategoryRepository::class, $repo);

        $this->login('user.admin');
        $crawler = $this->client->request('GET', '/event_category/search');
        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('btn-search')->form([
            'q' => 'eventCategory',
        ]);

        $responseCrawler = $this->client->submit($form);
        static::assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group anon
     * @group edit
     */
    public function testAnonEdit() : void {
        $crawler = $this->client->request('GET', '/event_category/1/edit');
        static::assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        static::assertTrue($this->client->getResponse()->isRedirect());
    }

    /**
     * @group edit
     * @group user
     */
    public function testUserEdit() : void {
        $this->login('user.user');
        $crawler = $this->client->request('GET', '/event_category/1/edit');
        static::assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group admin
     * @group edit
     */
    public function testAdminEdit() : void {
        $this->login('user.admin');
        $formCrawler = $this->client->request('GET', '/event_category/1/edit');
        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $form = $formCrawler->selectButton('Save')->form([
            'event_category[label]' => 'Updated Label',
            'event_category[description]' => 'Updated Description',
        ]);

        $this->client->submit($form);
        static::assertTrue($this->client->getResponse()->isRedirect('/event_category/1'));
        $responseCrawler = $this->client->followRedirect();
        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        static::assertSame(1, $responseCrawler->filter('td:contains("Updated Label")')->count());
        static::assertSame(1, $responseCrawler->filter('td:contains("Updated Description")')->count());
    }

    /**
     * @group anon
     * @group new
     */
    public function testAnonNew() : void {
        $crawler = $this->client->request('GET', '/event_category/new');
        static::assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        static::assertTrue($this->client->getResponse()->isRedirect());
    }

    /**
     * @group anon
     * @group new
     */
    public function testAnonNewPopup() : void {
        $crawler = $this->client->request('GET', '/event_category/new_popup');
        static::assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        static::assertTrue($this->client->getResponse()->isRedirect());
    }

    /**
     * @group new
     * @group user
     */
    public function testUserNew() : void {
        $this->login('user.user');
        $crawler = $this->client->request('GET', '/event_category/new');
        static::assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group new
     * @group user
     */
    public function testUserNewPopup() : void {
        $this->login('user.user');
        $crawler = $this->client->request('GET', '/event_category/new_popup');
        static::assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group admin
     * @group new
     */
    public function testAdminNew() : void {
        $this->login('user.admin');
        $formCrawler = $this->client->request('GET', '/event_category/new');
        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $form = $formCrawler->selectButton('Save')->form([
            'event_category[label]' => 'New Label',
            'event_category[description]' => 'New Description',
        ]);

        $this->client->submit($form);
        static::assertTrue($this->client->getResponse()->isRedirect());
        $responseCrawler = $this->client->followRedirect();
        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        static::assertSame(1, $responseCrawler->filter('td:contains("New Label")')->count());
        static::assertSame(1, $responseCrawler->filter('td:contains("New Description")')->count());
    }

    /**
     * @group admin
     * @group new
     */
    public function testAdminNewPopup() : void {
        $this->login('user.admin');
        $formCrawler = $this->client->request('GET', '/event_category/new_popup');
        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $form = $formCrawler->selectButton('Save')->form([
            'event_category[label]' => 'New Label',
            'event_category[description]' => 'New Description',
        ]);

        $this->client->submit($form);
        static::assertTrue($this->client->getResponse()->isRedirect());
        $responseCrawler = $this->client->followRedirect();
        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        static::assertSame(1, $responseCrawler->filter('td:contains("New Label")')->count());
        static::assertSame(1, $responseCrawler->filter('td:contains("New Description")')->count());
    }

    /**
     * @group admin
     * @group delete
     */
    public function testAdminDelete() : void {
        $repo = self::$container->get(EventCategoryRepository::class);
        $preCount = count($repo->findAll());

        $this->login('user.admin');
        $crawler = $this->client->request('GET', '/event_category/1');
        $form = $crawler->selectButton('Delete')->form();
        $this->client->submit($form);

        static::assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        static::assertTrue($this->client->getResponse()->isRedirect());
        $responseCrawler = $this->client->followRedirect();
        static::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $this->entityManager->clear();
        $postCount = count($repo->findAll());
        static::assertSame($preCount - 1, $postCount);
    }
}
