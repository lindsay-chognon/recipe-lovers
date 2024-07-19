<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomePageTest extends WebTestCase
{
    public function testHomePageBalises(): void
    {
        // create a client link a web browser
        $client = static::createClient();
        // request return a crawler for more complete tests
        // Make requests on DOM
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();

        $button = $crawler->filter('.btn');
        $this->assertEquals(1, count($button));

        $this->assertSelectorTextContains('h1', 'Bienvenue');
    }
}
