<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Form\IngredientType;
use App\Repository\IngredientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;

class IngredientController extends AbstractController
{
    /**
     * Display all ingredients with pagination
     *
     * @param IngredientRepository $repository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    #[Route('/ingredient', name: 'ingredient', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(
        IngredientRepository $repository, 
        PaginatorInterface $paginator,
        Request $request
        ): Response
    {

        /* cache */
        // try to get recipes probably alrady cached (system key / value)
        // if value is not found, we give the value with the collable (2nd arg of get)
        // More usefull on public recipes
        // More usefull on public recipes
        $cache = new FilesystemAdapter();
        $data = $cache->get('ingredient', function(ItemInterface $item) use ($repository) {
            // expiration
            $item->expiresAfter(15);
            $repository->findBy(['user' => $this->getUser()]);
        });

        $ingredients = $paginator->paginate(
            // to get only user ingredients
            // $this->>getUser get current user from symfony token
            $data,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('pages/ingredient/index.html.twig', [
            'ingredients' => $ingredients
        ]);
    }

    /**
     * Add new ingredient
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/ingredient/nouveau', name: 'ingredient.new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(
        Request $request,
        EntityManagerInterface $manager
        ): Response {

        $ingredient = new Ingredient();
        $form = $this->createForm(IngredientType::class, $ingredient);

        $form->handleRequest($request);

        // if the form is submit and valid comparated to different constraints in IngredientType
        if ($form->isSubmitted() && $form->isValid()) {
            $ingredient = $form->getData();
            $ingredient->setUser($this->getUser());
            $manager->persist($ingredient);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre ingrédient a été créé avec succès !'
            );

            return $this->redirectToRoute('ingredient');
        } else {
            $this->addFlash(
                'warning',
                'Il y a un problème.'
            );
        }

        return $this->render('pages/ingredient/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Edit ingredient
     *
     * @param Ingredient
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Security("is_granted('ROLE_USER') and user === ingredient.getUser()")]
    #[Route('ingredient/edition/{id}', 'ingredient.edit', methods: ['GET', 'POST'])]
     public function edit(
        Ingredient $ingredient,
        Request $request,
        EntityManagerInterface $manager
        ) : Response {
        
        // Need to create form with ingredient
        // Symfony automaticaly get the indredient's id from the entity with the param converter
        $form = $this->createForm(IngredientType::class, $ingredient);

        $form->handleRequest($request);

        // if the form is submit and valid comparated to different constraints in IngredientType
        if ($form->isSubmitted() && $form->isValid()) {
            $ingredient = $form->getData();
            $manager->persist($ingredient);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre ingrédient a été modifié avec succès !'
            );

            return $this->redirectToRoute('ingredient');
        } else {
            $this->addFlash(
                'warning',
                'Il y a un problème.'
            );
        }

        return $this->render('pages/ingredient/edit.html.twig', [
            'form' => $form->createView()
        ]);

    }

    /**
     * Delete ingredient
     *
     * @param Ingredient
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('ingredient/suppression/{id}', 'ingredient.delete', methods: ['GET'])]
    public function delete(
        EntityManagerInterface $manager,
        Ingredient $ingredient
        ) : Response {

            if (!$ingredient) {
                $this->addFlash(
                    'warning',
                    "L'ingrédient n'a pas été trouvé."
                );

                return $this->redirectToRoute('ingredient');
            }

        // TODO add modale to confirm deletion 

        $manager->remove($ingredient);
        $manager->flush();

        $this->addFlash(
            'success',
            'L\'ingrédient a été supprimé avec succès.<'
        );

        return $this->redirectToRoute('ingredient');

    }

}
