<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
#[UniqueEntity(fields: ['email'], message: 'Diese E-Mail-Adresse ist bereits registriert.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Assert\NotBlank(message: 'E-Mail ist erforderlich.')]
    #[Assert\Email(message: 'Bitte geben Sie eine gültige E-Mail-Adresse ein.')]
    private string $email;

    #[ORM\Column(type: 'string')]
    private string $password;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank(message: 'Vorname ist erforderlich.')]
    #[Assert\Length(min: 2, max: 100, minMessage: 'Vorname muss mindestens 2 Zeichen lang sein.')]
    private string $firstName;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank(message: 'Nachname ist erforderlich.')]
    #[Assert\Length(min: 2, max: 100, minMessage: 'Nachname muss mindestens 2 Zeichen lang sein.')]
    private string $lastName;

    #[ORM\OneToMany(targetEntity: UserAddress::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    private Collection $addresses;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->addresses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return Collection<int, UserAddress>
     */

    public function getAddresses(): Collection
    {
        return $this->addresses;
    }

    public function addAddress(UserAddress $address): static
    {
        if (!$this->addresses->contains($address)) {
            $this->addresses->add($address);
            $address->setUser($this);
        }
        return $this;
    }

    public function removeAddress(UserAddress $address): static
    {
        if ($this->addresses->contains($address)) {
            $this->addresses->removeElement($address);
            if ($address->getUser() === $this) {
                $address->setUser(null);
            }
        }
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
