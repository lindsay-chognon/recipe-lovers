<?php

namespace App\Tests\Functional;

use App\Entity\Ingredient;
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

    public function testIfListIngredientIsSuccesfull(): void {

        $client = static::createClient();
        $urlGenerator = $client->getContainer()->get('router');
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $user = $entityManager->find(User::class, '1');
        $client->loginUser($user);
        $client->request('GET', $urlGenerator->generate('ingredient'));

        $this->assertResponseIsSuccessful();

        $this->assertRouteSame('ingredient');
    }

    public function testIfEditIngredientIsSuccesfull(): void {
        $client = static::createClient();
        $urlGenerator = $client->getContainer()->get('router');
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $user = $entityManager->find(User::class, '1');
        $ingredient = $entityManager->getRepository(Ingredient::class)->findOneBy([
            'user' => $user,
        ]);

        $client->loginUser($user);

        $crawler = $client->request('GET', $urlGenerator->generate('ingredient.edit', ['id' => $ingredient->getId()]));

        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[name="ingredient"]')->form([
            'ingredient[name]' => 'Ingrédient de test',
            'ingredient[price]' => floatval(32),
        ]);

        $client->submit($form);

        $this->assertResponseStatusCodeSame(200);
    }

    public function testIfDeleteIngredientIsSuccesfull(): void {
        $client = static::createClient();
        $urlGenerator = $client->getContainer()->get('router');
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $user = $entityManager->find(User::class, '1');
        $ingredient = $entityManager->getRepository(Ingredient::class)->findOneBy([
            'user' => $user,
        ]);
        $client->loginUser($user);
        $client->request('GET', $urlGenerator->generate('ingredient.delete', ['id' => $ingredient->getId()]));

        $this->assertResponseStatusCodeSame(302);
    }
}
