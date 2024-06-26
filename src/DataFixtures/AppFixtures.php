<?php

namespace App\DataFixtures;

use App\Entity\Contact;
use App\Entity\Mark;
use App\Entity\Recipe;
use App\Entity\Ingredient;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class AppFixtures extends Fixture
{

    /**
     * @var Generator
     */
    private Generator $faker;

    public function __construct() {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void {

        // Users
        $users = [];

        $admin = new User();
        $admin->setFullName('Administrateur de Recipe lovers')
            ->setPseudo(null)
            ->setEmail('admin@recipe-lovers.com')
            ->setRoles(['ROLE_USER', 'ROLE_ADMIN'])
            ->setPassword('password');

        $users[] = $admin;
        $manager->persist($admin);

        // need to call UserPasswordHasherInterface to encode password via dependance injection
        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user
                ->setFullName($this->faker->name())
                ->setPseudo(mt_rand(0, 1) === 1 ? $this->faker->firstName() : null)
                ->setEmail($this->faker->email())
                ->setRoles(['ROLE_USER'])
                ->setPlainPassword('password');

            // useless method to hash password to much redundant
            // We finally use Entity listener
            /*  $hashedPassword = $this->hasher->hashPassword(
                $user,
                'password'
            );
            $user->setPassword($hashedPassword);
            */

            // stocking user before persist to use in next fixtures
            $users[] = $user;

            $manager->persist($user);

        }

        // ingredients
        // put ingredients in array to use in recipes
        $ingredients = [];
        for ($i = 0; $i <= 50; $i++) { 
            $ingredient = new Ingredient();
            $ingredient
                ->setName($this->faker->word())
                ->setPrice(mt_rand(1, 100))
                ->setUser($users[mt_rand(0, count($users) - 1)])
            ;

        $ingredients[] = $ingredient;
        $manager->persist($ingredient);

        }

        // recipes
        $recipes = [];
        for ($j = 0; $j < 25; $j++) { 
            $recipe = new Recipe();
            $recipe
                ->setName($this->faker->word())
                // time could be null
                // so if time is 1, mt_rand for time
                // else time is null
                ->setTime(mt_rand(0, 1) == 1 ? mt_rand(1, 1440) : null)
                ->setNbPeople(mt_rand(0, 1) == 1 ? mt_rand(1, 50) : null)
                ->setDifficulty(mt_rand(0, 1) == 1 ? mt_rand(1, 5) : null)
                ->setDescription($this->faker->text(300))
                ->setPrice(mt_rand(0, 1) == 1 ? mt_rand(1, 1000) : null)
                ->setIsFavorite(mt_rand(0, 1) == 1 ? true : false)
                ->setIsPublic(mt_rand(0, 1) == 1 ? true : false)
                ->setUser($users[mt_rand(0, count($users) - 1)])
            ;

            // for one recipe add between 5 and 15 ingredients
            for ($k=0; $k < mt_rand(5, 15); $k++) { 
                $recipe->addIngredient($ingredients[mt_rand(0, count($ingredients) - 1)]);
            }

            $recipes[] = $recipe;
            $manager->persist($recipe);

        }

        // Marks
        foreach($recipes as $recipe) {
            for ($i = 0; $i < mt_rand(1, 4); $i++) {
                $mark = new Mark();
                $mark
                    ->setMark(mt_rand(1, 5))
                    ->setUser($users[mt_rand(0, count($users) - 1)])
                    ->setRecipe($recipe);

                $manager->persist($mark);
            }
        }

        // Contacts
        for ($i = 0; $i < 30; $i++) {
            $contact = new Contact();
            $contact->setFullname($this->faker->name())
                ->setEmail($this->faker->email())
                ->setSubject('Demande n°'.($i+1))
                ->setMessage($this->faker->text());

            $manager->persist($contact);
        }

        $manager->flush();
    }
}
