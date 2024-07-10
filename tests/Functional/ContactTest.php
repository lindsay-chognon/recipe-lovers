<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ContactTest extends WebTestCase
{
    public function testSomething(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Formulaire de contact');

        // get form
        $submitButton = $crawler->selectButton('Envoyer');
        $form = $submitButton->form();

        $form["contact[fullname]"] = 'John Doe';
        $form["contact[email]"] = 'John.doe@example.com';
        $form["contact[subject]"] = 'Test subject';
        $form["contact[message]"] = 'Test message';

        // post form
        $client->submit($form);

        // check HTTP status
        $this->assertResponseStatusCodeSame(200);

        // check success message
       // $this->assertEmailCount(1);

       // $client->followRedirect();

       /* $this->assertSelectorTextContains(
            '.alert-success',
            'Votre message a bien été envoyé'
        );*/

    }

}
