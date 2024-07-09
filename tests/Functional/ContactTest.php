<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ContactTest extends WebTestCase
{
    public function testSomething(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Formulaire de contact');

        // get form
        $submitButton = $crawler->selectButton('Envoyer');
        $form = $submitButton->form();

        $form["contact[fullName]"] = 'John Doe';
        $form["contact[email]"] = 'John.doe@example.com';
        $form["contact[subject]"] = 'Test subject';
        $form["contact[message]"] = 'Test message';

        // post form
        $client->submit($form);

        // check HTTP status
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        // check success message
        $this->assertEmailCount(1);

        $client->followRedirect();

        $this->assertSelectorTextContains(
            '.div.alert.alert-success',
            'Votre demande a été envoyée avec succès !'
        );

    }

}
