<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Create admin',
)]
class CreateAdminCommand extends Command
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        parent::__construct('app:create-admin');
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('full_name', InputArgument::OPTIONAL, 'Full name')
            ->addArgument('email', InputArgument::OPTIONAL, 'Email')
            ->addArgument('password', InputArgument::OPTIONAL, 'Password')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');
        $io = new SymfonyStyle($input, $output);
        $fullname = $input->getArgument('full_name');
        if (!$fullname) {
            $question = new Question('Please enter your full name: ');
            $fullname = $helper->ask($input, $output, $question);
        }
        $email = $input->getArgument('email');
        if (!$email) {
            $question = new Question('Please enter your email: ');
            $email = $helper->ask($input, $output, $question);
        }
        $password = $input->getArgument('password');
        if (!$password) {
            $question = new Question('Please enter your password: ');
            $password = $helper->ask($input, $output, $question);
        }

        $user = new User();
        $user->setFullName($fullname)
            ->setEmail($email)
            ->setPlainPassword($password)
            ->setRoles(['ROLE_USER', 'ROLE_ADMIN']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success('Admin créé.');

        return Command::SUCCESS;
    }
}
