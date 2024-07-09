<?php

namespace App\Tests\Unit;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RecipeTest extends KernelTestCase
{
    public function testEntityIsValid(): void
    {
        self::bootKernel();

        $container = static::getContainer();
        $recipe = new Recipe();
        $recipe->setName('Recipe 1')
            ->setDescription('Description 1')
            ->setIsFavorite(true)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTimeImmutable());

        $errors = $container->get('validator')->validate($recipe);

        $this->assertCount(0, $errors);

    }

}
