<?php

namespace App\Tests\Functional;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class IngredientTest extends WebTestCase
{
    public function testIfCreateIngredientIsSuccesfull(): void
    {
        $client = static::createClient();

        // url generator
        $urlGenerator = $client->getContainer()->get('router');

        // entity manager
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $user = $entityManager->find(User::class, '1');
        $client->loginUser($user);

        // page de création de l'ingrédient
        $crawler = $client->request('GET', $urlGenerator->generate('ingredient.new'));

        // gérer le formulaire
        $form = $crawler->filter('form[name="ingredient"]')->form([
            'ingredient[name]' => 'Ingrédient de test',
            'ingredient[price]' => floatval(45),
        ]);
        $client->submit($form);

        $this->assertResponseStatusCodeSame(200);

    }
}
