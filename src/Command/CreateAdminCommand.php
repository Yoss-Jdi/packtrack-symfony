<?php

namespace App\Command;

use App\Entity\Role;
use App\Entity\Utilisateurs;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Create a new admin user',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Create Admin Account');

        // Ask for email
        $emailQuestion = new Question('Email address: ', 'admin@packtrack.com');
        $email = $io->askQuestion($emailQuestion);

        // Check if user already exists
        $existingUser = $this->entityManager->getRepository(Utilisateurs::class)
            ->findOneBy(['Email' => $email]);

        if ($existingUser) {
            $io->error('A user with this email already exists!');
            return Command::FAILURE;
        }

        // Ask for password
        $passwordQuestion = new Question('Password: ');
        $passwordQuestion->setHidden(true);
        $passwordQuestion->setHiddenFallback(false);
        $password = $io->askQuestion($passwordQuestion);

        if (empty($password)) {
            $io->error('Password cannot be empty!');
            return Command::FAILURE;
        }

        // Ask for first name
        $prenomQuestion = new Question('First name: ', 'Admin');
        $prenom = $io->askQuestion($prenomQuestion);

        // Ask for last name
        $nomQuestion = new Question('Last name: ', 'System');
        $nom = $io->askQuestion($nomQuestion);

        // Ask for phone (optional)
        $phoneQuestion = new Question('Phone (optional): ', null);
        $phone = $io->askQuestion($phoneQuestion);

        // Create admin user
        $admin = new Utilisateurs();
        $admin->setEmail($email);
        $admin->setPrenom($prenom);
        $admin->setNom($nom);
        $admin->setRole(Role::ADMIN);
        
        if ($phone) {
            $admin->setTelephone($phone);
        }

        // Hash password
        $hashedPassword = $this->passwordHasher->hashPassword($admin, $password);
        $admin->setMotDePasse($hashedPassword);

        // Save to database
        $this->entityManager->persist($admin);
        $this->entityManager->flush();

        $io->success([
            'Admin account created successfully!',
            'Email: ' . $email,
            'Name: ' . $prenom . ' ' . $nom,
            'Role: ADMIN',
        ]);

        $io->warning('Please change the password after first login!');

        return Command::SUCCESS;
    }
}
