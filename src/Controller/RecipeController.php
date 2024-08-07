<?php

namespace App\Controller;

use App\Entity\Mark;
use App\Entity\Recipe;
use App\Form\MarkType;
use App\Form\RecipeType;
use App\Repository\MarkRepository;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Cache\ItemInterface;

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

    #[Route('/recette/public', 'recipe.index.public', methods: ['GET'])]
    public function indexPublic(
        RecipeRepository $repository,
        PaginatorInterface $paginator,
        Request $request
    ) : Response {

        /* cache */
        // try to get recipes probably alrady cached (system key / value)
        // if value is not found, we give the value with the collable (2nd arg of get)
        $cache = new FilesystemAdapter();
        $data = $cache->get('recipes', function(ItemInterface $item) use ($repository) {
            // expiration
            $item->expiresAfter(15);
            return $repository->findPublicRecipes(null);

        });

        $recipes = $paginator->paginate(
            $data,
            $request->query->getInt('pages', 1)
        );

        return $this->render('pages/recipe/index_public.html.twig', [
            'recipes' => $recipes
        ]);
    }

    /**
     * Display a recipe if this one is public
     * @param Recipe $recipe
     * @return Response
     */
    #[Security("is_granted('ROLE_USER') or recipe.isIsPublic() === true")]
    #[Route('/recette/{id}', 'recipe.show', methods: ['GET', 'POST'])]
    public function show(
        Recipe $recipe,
        Request $request,
        MarkRepository $markRepository,
        EntityManagerInterface $entityManager,
    ) : Response {

        $mark = new Mark();
        $form = $this->createForm(MarkType::class, $mark);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $mark->setUser($this->getUser())
                ->setRecipe($recipe);

            $existingMark = $markRepository->findOneBy([
                'user' => $this->getUser(),
                'recipe' => $recipe
            ]);

            if (!$existingMark) {
                $entityManager->persist($mark);
            } else {
                $existingMark->setMark(
                    $form->getData()->getMark()
                );
                $entityManager->persist($mark);
            }
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Votre note a bien été prise en compte.'
            );

            return $this->redirectToRoute('recipe.show', ['id' => $recipe->getId()]);
        }

        // calculation for mark bar
        $markPercent = ($recipe->getAverageRating() / 5) * 100;
        return $this->render('pages/recipe/show.html.twig', [
            'recipe' => $recipe,
            'markPercent' => $markPercent,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Add new recipe
     *
     * @param Request
     * @param EntityManagerInterface
     * @return Response
     */
    // TODO : bug sur la route new
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
