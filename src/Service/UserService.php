<?php

namespace App\Service;

use App\Entity\User;
use App\DTO\UserRegistrationDto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator
    ) {}

    public function registerUser(UserRegistrationDto $dto): User
    {
        // Prüfen ob E-Mail bereits existiert
        $existingUser = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => $dto->email]);

        if ($existingUser) {
            throw new \InvalidArgumentException('Diese E-Mail-Adresse ist bereits registriert.');
        }

        $user = new User();
        $user->setEmail($dto->email);
        $user->setFirstName($dto->firstName);
        $user->setLastName($dto->lastName);

        // Passwort hashen
        $hashedPassword = $this->passwordHasher->hashPassword($user, $dto->password);
        $user->setPassword($hashedPassword);

        // Validierung
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            throw new \InvalidArgumentException('Validierungsfehler: ' . (string) $errors);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function findUserByEmail(string $email): ?User
    {
        return $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => $email]);
    }
}
