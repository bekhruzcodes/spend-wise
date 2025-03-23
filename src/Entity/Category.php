<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\Table(name: "category")]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["category:read"])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'categories')]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", nullable: true, onDelete: "SET NULL")]
    private ?User $user = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[Groups(["category:read", "category:write"])]
    private ?string $name = null;

    #[ORM\Column(length: 7, options: ["default" => "#000000"])]
    #[Assert\Regex(pattern: "/^#[0-9a-fA-F]{6}$/", message: "Color must be a valid hex color code")]
    #[Groups(["category:read", "category:write"])]
    private ?string $color = "#000000";

    #[ORM\Column(options: ["default" => false])]
    #[Groups(["category:read", "category:write"])]
    private ?bool $isSystem = false;

    #[ORM\Column(options: ["default" => false])]
    #[Groups(["category:read", "category:write"])]
    private ?bool $isIncome = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(["category:read"])]
    private ?\DateTimeInterface $createdAt = null;

    // #[ORM\OneToMany(mappedBy: 'category', targetEntity: Transaction::class)]
    // private Collection $transactions;

    // #[ORM\OneToMany(mappedBy: 'category', targetEntity: BudgetCategory::class)]
    // private Collection $budgetCategories;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->transactions = new ArrayCollection();
        $this->budgetCategories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color ?? "#000000";
        return $this;
    }

    public function isSystem(): ?bool
    {
        return $this->isSystem;
    }

    public function setIsSystem(bool $isSystem): self
    {
        $this->isSystem = $isSystem;
        return $this;
    }

    public function isIncome(): ?bool
    {
        return $this->isIncome;
    }

    public function setIsIncome(bool $isIncome): self
    {
        $this->isIncome = $isIncome;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    // /**
    //  * @return Collection<int, Transaction>
    //  */
    // public function getTransactions(): Collection
    // {
    //     return $this->transactions;
    // }

    // /**
    //  * @return Collection<int, BudgetCategory>
    //  */
    // public function getBudgetCategories(): Collection
    // {
    //     return $this->budgetCategories;
    // }
}