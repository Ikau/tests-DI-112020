<?php

namespace App\Entity;

use App\Repository\CustomerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CustomerRepository::class)
 */
class Customer
{
    const COLUMN_NAME_CITY = 'city';
    const COLUMN_NAME_EMAIL = 'email';
    const COLUMN_NAME_FIRST_NAME = 'firstname';
    const COLUMN_NAME_ID = 'id';
    const COLUMN_NAME_LAST_NAME = 'lastname';
    const COLUMN_NAME_POSTAL_CODE = 'postal_code';
    const COLUMN_NAME_TITLE = 'title';

    /**
     * @ORM\Id
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

    /**
     * @ORM\OneToMany(targetEntity=Order::class, mappedBy="customer", orphanRemoval=true)
     */
    private $orders;

    public function __construct()
    {
        $this->orders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
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

    /**
     * @return Collection|Order[]
     */
    public function gerOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): self
    {
        if (!$this->orders->contains($order)) {
            $this->orders[] = $order;
            $order->setCustomer($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): self
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getCustomer() === $this) {
                $order->setCustomer(null);
            }
        }

        return $this;
    }

    /**
     * @return string Human readable title string.
     */
    public function getTitleName(): string
    {
        return $this->title === 1
            ? 'mme'
            : 'm';
    }

    /**
     * @return array Associative array as [column_name => column_value, ...]
     */
    public function toAssociativeArray(): array
    {
        return [
            Customer::COLUMN_NAME_ID => $this->getId(),
            Customer::COLUMN_NAME_TITLE => $this->getTitleName(),
            Customer::COLUMN_NAME_LAST_NAME => $this->getLastname() ?? '',
            Customer::COLUMN_NAME_FIRST_NAME => $this->getFirstname() ?? '',
            Customer::COLUMN_NAME_POSTAL_CODE => $this->getPostalCode() ?? '',
            Customer::COLUMN_NAME_CITY => $this->getCity() ?? '',
            Customer::COLUMN_NAME_EMAIL => $this->getEmail() ?? ''
        ];
    }
}
