<?php

namespace App\Tests\Unit;

use App\Entity\Recipe;
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
}
