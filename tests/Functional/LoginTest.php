<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class LoginTest extends WebTestCase
{
    public function testIfLoginIsSuccessfull(): void
    {
        $client = static::createClient();

        // get route with url generator
        $urlGenerator = $client->getContainer()->get("router");
        $crawler = $client->request('GET', $urlGenerator->generate("security.login"));

        // manage form
        $form = $crawler->filter('form[name=login]')->form([
                "_username" => "admin@recipe-lovers.com",
                "_password" => "password"
            ]
        );

        $client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();
    }

    public function testIsLoginFailedWhenPasswordIsWrong(): void {
        $client = static::createClient();

        // get route with url generator
        $urlGenerator = $client->getContainer()->get("router");
        $crawler = $client->request('GET', $urlGenerator->generate("security.login"));

        // manage form
        $form = $crawler->filter('form[name=login]')->form([
                "_username" => "admin@recipe-lovers.com",
                "_password" => "password_"
            ]
        );

        $client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $this->assertRouteSame('security.login');

    }
}
