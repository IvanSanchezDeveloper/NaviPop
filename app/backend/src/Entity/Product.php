<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use http\Exception\InvalidArgumentException;

#[ORM\Table(name: 'product', schema: 'app')]
#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    private const PRODUCT_NAME_LENGTH = 30;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: self::PRODUCT_NAME_LENGTH)]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $imagePath = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $userSeller = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $price = null;

    #[ORM\Column]
    private ?\DateTime $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        if (strlen($name) > self::PRODUCT_NAME_LENGTH) {
            throw new InvalidArgumentException("Product name must be less than " . self::PRODUCT_NAME_LENGTH . " characters.");
        }

        $this->name = $name;

        return $this;
    }

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(string $imagePath): static
    {
        $this->imagePath = $imagePath;

        return $this;
    }

    public function getUserSeller(): ?User
    {
        return $this->userSeller;
    }

    public function setUserSeller(?User $userSeller): static
    {
        $this->userSeller = $userSeller;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        if (!is_numeric($price) || $price <= 0) {
            throw new InvalidArgumentException("Price must be a positive number.");
        }

        $this->price = $price;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
