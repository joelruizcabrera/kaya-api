<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class UserLoginDto
{
    #[Assert\NotBlank(message: 'E-Mail ist erforderlich.')]
    #[Assert\Email(message: 'Bitte geben Sie eine gültige E-Mail-Adresse ein.')]
    public string $email;

    #[Assert\NotBlank(message: 'Passwort ist erforderlich.')]
    public string $password;
}
