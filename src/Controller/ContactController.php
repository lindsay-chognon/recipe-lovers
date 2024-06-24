<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/contact', name: 'contact')]
    public function index(
        Request $request,
        EntityManagerInterface $manager,
        MailService $mailService
    ): Response
    {
        $contact = new Contact();

        if ($this->getUser()) {
            $contact->setFullname($this->getUser()->getFullname())
                ->setEmail($this->getUser()->getEmail());
        }

        $form = $this->createForm(ContactType::class, $contact);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $contact = $form->getData();

            $manager->persist($contact);

            $manager->flush();

            $mailService->sendMail(
                $contact->getEmail(),
                $contact->getSubject(),
                'email/contact.html.twig',
                ["contact" => $contact]
            );

            // email
            $email = (new TemplatedEmail())
                ->from($contact->getEmail())
                ->to('admin@recipe-lovers.com')
                ->subject($contact->getSubject())
                ->htmlTemplate('email/contact.html.twig')
                ->context([
                    'contact' => $contact,
                ]);

            $mailer->send($email);

            $this->addFlash(
                'success',
                'Votre message a bien été envoyé.'
            );

            return $this->redirectToRoute('contact');
        }
        return $this->render('pages/contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
