<?php

namespace App\Entity;

use App\Repository\CustomerRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CustomerRepository::class)
 */
class Customer
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     * @var ?string
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var ?string
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var ?string
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var ?string
     */
    private $postal_code;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var ?string
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var ?string
     */
    private $email;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?int
    {
        return $this->title;
    }

    public function setTitle(?int $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postal_code;
    }

    public function setPostalCode(?string $postal_code): self
    {
        $this->postal_code = $postal_code;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }
}
