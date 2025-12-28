<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity()]
#[ORM\Table(name: 'users')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Email не должен быть пустым')]
    #[Assert\Email(message: 'Email должен быть корректным')]
    private string $email;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Имя не должно быть пустым')]
    #[Assert\Length(
        min: 2,
        max: 30,
        minMessage: 'Имя должно быть не менее {{ limit }} символов',
        maxMessage: 'Имя должно быть не более {{ limit }} символов'
    )]
    #[Assert\Regex(
        pattern: '/^[а-яёА-ЯЁa-zA-Z\-\s]+$/u',
        message: 'Имя может содержать только буквы, дефисы и пробелы'
    )]
    private string $name;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Пароль не должен быть пустым')]
    #[Assert\Length(
        min: 8,
        minMessage: 'Пароль должен быть не менее {{ limit }} символов'
    )]
    #[Assert\Regex(
        pattern: '/^(?=.*[a-zA-Zа-яёА-ЯЁ])(?=.*\d)/u',
        message: 'Пароль должен содержать буквы и цифры'
    )]
    private string $password;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column]
    private bool $isAdmin = false;

    /**
     * @var Collection<int, Recipe>
     */
    #[ORM\OneToMany(targetEntity: Recipe::class, mappedBy: 'user')]
    private Collection $recipes;

    /**
     * @var Collection<int, Review>
     */
    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'user')]
    private Collection $reviews;

    #[ORM\Column]
    private bool $isActive = true;

    public function __construct(
        string $email = '',
        string $name = '',
        string $password = '',
        bool $isAdmin = false,
        ?int $id = null,
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->name = $name;
        $this->password = $password;
        $this->isAdmin = $isAdmin;
        $this->createdAt = new DateTimeImmutable();
        $this->recipes = new ArrayCollection();
        $this->reviews = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getRoles(): array
    {
        return $this->isAdmin
            ? ['ROLE_USER', 'ROLE_ADMIN']
            : ['ROLE_USER'];
    }

    public function eraseCredentials(): void {}

    /**
     * @return non-empty-string
     */
    public function getUserIdentifier(): string
    {
        return '' === $this->email ? 'unknown' : $this->email;
    }

    public function isAdmin(): bool
    {
        return $this->isAdmin;
    }

    /**
     * @return Collection<int, Recipe>
     */
    public function getRecipes(): Collection
    {
        return $this->recipes;
    }

    public function addRecipe(Recipe $recipe): static
    {
        if (!$this->recipes->contains($recipe)) {
            $this->recipes->add($recipe);
            $recipe->setUser($this);
        }

        return $this;
    }

    public function removeRecipe(Recipe $recipe): static
    {
        $this->recipes->removeElement($recipe);

        return $this;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): static
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setUser($this);
        }

        return $this;
    }

    public function removeReview(Review $review): static
    {
        $this->reviews->removeElement($review);

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }
}
