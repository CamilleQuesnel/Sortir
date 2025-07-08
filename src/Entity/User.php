<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['mail'], message: 'Cet email est déjà utilisé.')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_PSEUDO', fields: ['pseudo'])]
#[UniqueEntity(fields: ['pseudo'], message: 'There is already an account with this pseudo')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[Assert\NotBlank(message: 'Please enter your pseudo')]
    #[Assert\Length(max: 180, maxMessage: 'Too long ! 180 characters at most !')]
    #[Assert\Length(min: 2, minMessage: 'Too short ! 2 characters at most !')]
    #[ORM\Column(length: 180)]
    private ?string $pseudo = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */

    #[ORM\Column]
    private ?string $password = null;

    #[Assert\NotBlank(message: 'Please enter your first name')]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: 'Your first name must be at least {{ limit }} characters long',
        maxMessage: 'Your first name cannot be longer than {{ limit }} characters'
    )]
    #[Assert\Regex(
        pattern: '/^[\p{L}\'\- ]+$/u',
        message: 'Your first name can only contain letters, spaces, apostrophes and hyphens'
    )]
    #[ORM\Column(length: 50)]
    private ?string $firstName = null;
    #[Assert\NotBlank(message: 'Please enter your last name')]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: 'Your last name must be at least {{ limit }} characters long',
        maxMessage: 'Your last name cannot be longer than {{ limit }} characters'
    )]
    #[Assert\Regex(
        pattern: '/^[\p{L}\'\- ]+$/u',
        message: 'Your last name can only contain letters, spaces, apostrophes and hyphens'
    )]
    #[ORM\Column(length: 50)]
    private ?string $lastName = null;

    #[Assert\NotBlank(message: 'Please enter your email address')]
    #[Assert\Email(
        message: 'The email "{{ value }}" is not a valid email.',
        mode: 'html5'
    )]
    #[Assert\Length(
        max: 180,
        maxMessage: 'The email should not be longer than {{ limit }} characters'
    )]
    #[ORM\Column(length: 100, unique: true)]
    private ?string $mail = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $image = null;


    #[Assert\NotBlank(message: 'Please enter your phone number')]
    #[Assert\Length(
        min: 10,
        max: 15,
        minMessage: 'The phone number must be at least {{ limit }} digits',
        maxMessage: 'The phone number cannot exceed {{ limit }} digits'
    )]
    #[Assert\Regex(
        pattern: '/^\+?[0-9]{10,15}$/',
        message: 'Please enter a valid phone number (digits only, with optional +)'
    )]
    #[ORM\Column(length: 15, nullable: true)]
    private ?string $phoneNumber = null;

    #[ORM\Column]
    private ?bool $admin = null;

    #[ORM\Column]
    private ?bool $active = null;
    #[ORM\Column(nullable: true)]
    private ?string $resetToken = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $resetRequestedAt = null;

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): void
    {
        $this->resetToken = $resetToken;
    }

    public function getResetRequestedAt(): ?\DateTimeInterface
    {
        return $this->resetRequestedAt;
    }

    public function setResetRequestedAt(?\DateTimeInterface $resetRequestedAt): void
    {
        $this->resetRequestedAt = $resetRequestedAt;
    }

    /**
     * @var Collection<int, Hangout>
     */
    #[ORM\ManyToMany(targetEntity: Hangout::class, mappedBy: 'users')]
    private Collection $hangout;

    /**
     * @var Collection<int, Hangout>
     */
    #[ORM\OneToMany(targetEntity: Hangout::class, mappedBy: 'organizer', orphanRemoval: true)]
    private Collection $organizer;

    #[ORM\ManyToOne(inversedBy: 'user')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Campus $campus = null;

    public function __construct()
    {
        $this->hangout = new ArrayCollection();
        $this->organizer = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string)$this->pseudo;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): static
    {
        $this->mail = $mail;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function isAdmin(): ?bool
    {
        return $this->admin;
    }

    public function setAdmin(bool $admin): static
    {
        $this->admin = $admin;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return Collection<int, Hangout>
     */
    public function getHangout(): Collection
    {
        return $this->hangout;
    }

    public function addHangout(Hangout $hangout): static
    {
        if (!$this->hangout->contains($hangout)) {
            $this->hangout->add($hangout);
        }

        return $this;
    }

    public function removeHangout(Hangout $hangout): static
    {
        $this->hangout->removeElement($hangout);

        return $this;
    }

    /**
     * @return Collection<int, Hangout>
     */
    public function getOrganizer(): Collection
    {
        return $this->organizer;
    }

    public function addOrganizer(Hangout $organizer): static
    {
        if (!$this->organizer->contains($organizer)) {
            $this->organizer->add($organizer);
            $organizer->setOrganizer($this);
        }

        return $this;
    }

    public function removeOrganizer(Hangout $organizer): static
    {
        if ($this->organizer->removeElement($organizer)) {
            // set the owning side to null (unless already changed)
            if ($organizer->getOrganizer() === $this) {
                $organizer->setOrganizer(null);
            }
        }

        return $this;
    }

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): static
    {
        $this->campus = $campus;

        return $this;
    }
}
