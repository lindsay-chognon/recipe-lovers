<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{

    /**
     * Edit user profile
     *
     * @param User $user
     * @param EntityManagerInterface $manager
     * @param Request $request
     * @return Response
     */
    #[Route('/utilisateur/edition/{id}', name: 'user.edit', methods: ['GET', 'POST'])]
    public function edit(
        User $user, 
        Request $request,
        EntityManagerInterface $manager
        ): Response
    {
        // we check is user is logged
        if (!$this->getUser()) {
            return $this->redirectToRoute('security.login');
        }

        //current user is same as user get by ID in route
        if ($this->getUser() !== $user) {
            return $this->redirectToRoute('recipe');
        }

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $manager->persist($user);
            $manager->flush();

            $this->addFlash(
                'success',
                'Les informations de votre compte ont bien été modifiées.'
            );


            return $this->redirectToRoute('recipe');
        }

        return $this->render('pages/user/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
