<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Form\RecipeType;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RecipeController extends AbstractController
{
    /**
     * Display all recipes with pagination
     *
     * @param IngredientRepository $repository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    #[Route('/recette', name: 'recipe', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(
        RecipeRepository $repository,
        PaginatorInterface $paginator,
        Request $request
        ): Response
    {

        $recipes = $paginator->paginate(
            $repository->findBy(['user' => $this->getUser()]),
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('pages/recipe/index.html.twig', [
            'recipes' => $recipes,
        ]);
    }

    #[Security("is_granted('ROLE_USER') and recipe.isIsPublic() === true")]
    #[Route('/recette/{id}', 'recipe.show', methods: ['GET'])]
    public function show(Recipe $recipe) : Response {
        return $this->render('pages/recipe/show.html.twig', [
            'recipe' => $recipe
        ]);
    }

    /**
     * Add new recipe
     *
     * @param Request
     * @param EntityManagerInterface
     * @return Response
     */
    #[Route('/recette/nouveau', name: 'recipe.new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(
        Request $request,
        EntityManagerInterface $manager
        ): Response {

        $recipe = new Recipe();

        $form = $this->createForm(RecipeType::class, $recipe);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $recipe = $form->getData();
            $recipe->setUser($this->getUser());

            $manager->persist($recipe);
            $manager->flush($recipe);

            $this->addFlash(
                'success',
                'Votre recette a été créée avec succès !'
            );

            return $this->redirectToRoute('recipe');

        } else {

            $this->addFlash(
                'warning',
                'Il y a un problème.'
            );

        }

        return $this->render('pages/recipe/new.html.twig', [
            'form' => $form->createView()
        ]);

    }

    /**
     * Edit recipe
     *
     * @param Ingredient
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('recette/edition/{id}', 'recipe.edit', methods: ['GET', 'POST'])]
    #[Security("is_granted('ROLE_USER') and user === recipe.getUser()")]
     public function edit(
        Recipe $recipe,
        Request $request,
        EntityManagerInterface $manager
        ) : Response {
        
        // Need to create form with recipe
        // Symfony automaticaly get the recipe's id from the entity with the param converter
        $form = $this->createForm(RecipeType::class, $recipe);

        $form->handleRequest($request);

        // if the form is submit and valid comparated to different constraints in RecipeType
        if ($form->isSubmitted() && $form->isValid()) {
            $recipe = $form->getData();
            $manager->persist($recipe);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre recette a été modifiée avec succès !'
            );

            return $this->redirectToRoute('recipe');
        } else {
            $this->addFlash(
                'warning',
                'Il y a un problème.'
            );
        }

        return $this->render('pages/recipe/edit.html.twig', [
            'form' => $form->createView()
        ]);

    }

    /**
     * Delete recipe
     *
     * @param Ingredient
     * @param EntityManagerInterface
     * @return Response
     */
    #[Route('recette/suppression/{id}', 'recipe.delete', methods: ['GET'])]
    public function delete(
        EntityManagerInterface $manager,
        Recipe $recipe
        ) : Response {

            if (!$recipe) {
                $this->addFlash(
                    'warning',
                    "La recette n'a pas été trouvée."
                );

                return $this->redirectToRoute('recipe');
            }

        // TODO add modale to confirm deletion 

        $manager->remove($recipe);
        $manager->flush();

        $this->addFlash(
            'success',
            'La recette a été supprimée avec succès.<'
        );

        return $this->redirectToRoute('recipe');

    }

}
