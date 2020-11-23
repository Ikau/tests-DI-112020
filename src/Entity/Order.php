<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OrderRepository::class)
 * @ORM\Table(name="`order`")
 */
class Order implements \EntitySerializationInterface
{
    const COLUMN_NAME_ID = 'purchase_identifier';
    const COLUMN_NAME_CURRENCY = 'currency';
    const COLUMN_NAME_CUSTOMER_ID = 'customer_id';
    const COLUMN_NAME_DATE = 'date';
    const COLUMN_NAME_PRICE = 'price';
    const COLUMN_NAME_PRODUCT_ID = 'product_id';
    const COLUMN_NAME_QUANTITY = 'quantity';

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $purchase_identifier;

    /**
     * @ORM\ManyToOne(targetEntity=Customer::class, inversedBy="orders")
     * @ORM\JoinColumn(nullable=false)
     * @var Customer
     */
    private $customer;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $product_id;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $quantity;

    /**
     * @ORM\Column(type="float")
     * @var float
     */
    private $price;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $currency;

    /**
     * @ORM\Column(type="date")
     */
    private $date;

    public function getPurchaseIdentifier(): ?string
    {
        return $this->purchase_identifier;
    }

    public function setPurchaseIdentifier(string $purchase_identifier): self
    {
        $this->purchase_identifier = $purchase_identifier;

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    public function getProductId(): ?int
    {
        return $this->product_id;
    }

    public function setProductId(int $product_id): self
    {
        $this->product_id = $product_id;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function toAssociativeArray(): array
    {
        return [
            self::COLUMN_NAME_ID => $this->getPurchaseIdentifier(),
            self::COLUMN_NAME_CURRENCY => $this->getCurrency(),
            self::COLUMN_NAME_CUSTOMER_ID => $this->getCustomer()->getId(),
            self::COLUMN_NAME_DATE => $this->getDate()->format('Y-m-d'),
            self::COLUMN_NAME_PRICE => $this->getPrice(),
            self::COLUMN_NAME_PRODUCT_ID => $this->getProductId(),
            self::COLUMN_NAME_QUANTITY => $this->getQuantity()
        ];
    }
}
