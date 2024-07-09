<?php

namespace App\Tests\Unit;

use App\Entity\Mark;
use App\Entity\Recipe;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RecipeTest extends KernelTestCase
{
    // DRY
    public function getEntity() : Recipe {
        $recipe = new Recipe();
        $recipe->setName('Name 1');
        $recipe->setDescription('Description 1');
        $recipe->isIsFavorite(true);
        $recipe->setCreatedAt(new \DateTimeImmutable());
        $recipe->setUpdatedAt(new \DateTimeImmutable());

        return $recipe;
    }
    public function testEntityIsValid(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        $recipe = $this->getEntity();

        $errors = $container->get('validator')->validate($recipe);

        $this->assertCount(0, $errors);

    }

    public function testInvalidName() {
        self::bootKernel();
        $container = static::getContainer();

        $recipe = $this->getEntity();
        $recipe->setName('');

        $errors = $container->get('validator')->validate($recipe);

        $this->assertCount(2, $errors);

    }

    public function testGetAverageRating() {
        $recipe = $this->getEntity();
        $user = static::getContainer()->get('doctrine.orm.entity_manager')->find(User::class, 1);

        for ($i = 0; $i < 10; $i++) {
            $mark = new Mark();
            $mark->setMark(2);
            $mark->setUser($user);
            $mark->setRecipe($recipe);
            $recipe->addMark($mark);
        }

        $this->assertTrue(2.0 === $recipe->getAverageRating());
    }
}
