<?php

namespace App\DTO;
use Symfony\Component\Validator\Constraints as Assert;
class UserAddressDto
{
    #[Assert\NotBlank(message: 'Street is required.')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Street cannot be longer than {{ limit }} characters'
    )]
    public ?string $street;

    #[Assert\NotBlank(message: 'Postcode is required')]
    #[Assert\Length(
        max: 10,
        maxMessage: 'Postcode cannot be longer than {{ limit }} characters'
    )]
    public ?string $postcode = null;

    #[Assert\NotBlank(message: 'City is required')]
    #[Assert\Length(
        max: 100,
        maxMessage: 'City cannot be longer than {{ limit }} characters'
    )]
    public ?string $city = null;

    #[Assert\NotBlank(message: 'Housenumber is required')]
    #[Assert\Length(
        max: 10,
        maxMessage: 'Housenumber cannot be longer than {{ limit }} characters'
    )]
    public ?string $housenumber = null;

    #[Assert\Length(
        max: 100,
        maxMessage: 'Additional cannot be longer than {{ limit }} characters'
    )]
    public ?string $additional = null;
}
