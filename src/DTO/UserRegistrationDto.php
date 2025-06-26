<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class UserRegistrationDto
{
    #[Assert\NotBlank(message: 'E-Mail ist erforderlich.')]
    #[Assert\Email(message: 'Bitte geben Sie eine gültige E-Mail-Adresse ein.')]
    public string $email;

    #[Assert\NotBlank(message: 'Passwort ist erforderlich.')]
    #[Assert\Length(min: 8, minMessage: 'Passwort muss mindestens 8 Zeichen lang sein.')]
    #[Assert\Regex(
        pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/',
        message: 'Passwort muss mindestens einen Kleinbuchstaben, einen Großbuchstaben und eine Zahl enthalten.'
    )]
    public string $password;

    #[Assert\NotBlank(message: 'Vorname ist erforderlich.')]
    #[Assert\Length(min: 2, max: 100, minMessage: 'Vorname muss mindestens 2 Zeichen lang sein.')]
    public string $firstName;

    #[Assert\NotBlank(message: 'Nachname ist erforderlich.')]
    #[Assert\Length(min: 2, max: 100, minMessage: 'Nachname muss mindestens 2 Zeichen lang sein.')]
    public string $lastName;
}
