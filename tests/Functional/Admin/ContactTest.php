<?php

namespace Tests\Functional\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ContactTest extends WebTestCase {

    public function testCrudIsHere(): void {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $user = $entityManager->getRepository(User::class)->findOneBy(['id' => 1]);

        $client->loginUser($user);

        $client->request('GET', '/admin');

        $this->assertResponseIsSuccessful();

        $crawler = $client->clickLink('Demandes de contact');

        $client->click($crawler->filter('.action-new')->link());
        $this->assertResponseIsSuccessful();

        $client->request('GET', '/admin');
        $client->click($crawler->filter('.action-edit')->link());
        $this->assertResponseIsSuccessful();
    }

}