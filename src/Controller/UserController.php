<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/utilisateur/edition/{id}', name: 'user.edit')]
    public function edit(User $user): Response
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

        return $this->render('pages/user/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
