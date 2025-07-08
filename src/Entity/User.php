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
//contraintes d'unicité
#[UniqueEntity(fields: ['mail'], message: 'Cet email est déjà utilisé.')]
#[UniqueEntity(fields: ['pseudo'], message: 'Ce pseudo est déjà utilisé.')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_PSEUDO', fields: ['pseudo'])]

class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[Assert\NotBlank(message: 'Veuillez saisir un pseudo.')]
    #[Assert\Length(
        min: 2,
        max: 180,
        minMessage: 'Le pseudo doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le pseudo ne peut pas dépasser {{ limit }} caractères.'
    )]
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
    //ne pas mettre d'assert ici ça se passe dans le formulaire sur plainpassword
    private ?string $password = null;

    #[Assert\NotBlank(message: 'Veuillez saisir votre prénom.')]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: 'Le prénom doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le prénom ne peut pas dépasser {{ limit }} caractères.'
    )]
    #[Assert\Regex(
        pattern: '/^[\p{L}\'\- ]+$/u',
        message: 'Le prénom ne peut contenir que des lettres, des espaces, des apostrophes ou des tirets.'
    )]
    #[ORM\Column(length: 50)]
    private ?string $firstName = null;
    #[Assert\NotBlank(message: 'Veuillez saisir votre nom de famille.')]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: 'Le nom de famille doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le nom de famille ne peut pas dépasser {{ limit }} caractères.'
    )]
    #[Assert\Regex(
        pattern: '/^[\p{L}\'\- ]+$/u',
        message: 'Le nom de famille ne peut contenir que des lettres, des espaces, des apostrophes ou des tirets.'
    )]
    #[ORM\Column(length: 50)]
    private ?string $lastName = null;

    #[Assert\NotBlank(message: 'Veuillez saisir une adresse email.')]
    #[Assert\Email(
        message: 'L\'adresse "{{ value }}" n\'est pas valide.',
        mode: 'html5'
    )]
    #[Assert\Length(
        max: 100,
        maxMessage: 'L\'email ne peut pas dépasser {{ limit }} caractères.'
    )]
    #[ORM\Column(length: 100, unique: true)]
    private ?string $mail = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $image = null;


    #[Assert\NotBlank(message: 'Veuillez saisir un numéro de téléphone.')]
    #[Assert\Length(
        min: 10,
        max: 15,
        minMessage: 'Le numéro de téléphone doit contenir au moins {{ limit }} chiffres.',
        maxMessage: 'Le numéro de téléphone ne peut pas dépasser {{ limit }} chiffres.'
    )]
    #[Assert\Regex(
        pattern: '/^\+?[0-9]{10,15}$/',
        message: 'Veuillez entrer un numéro de téléphone valide (chiffres uniquement, avec un "+" éventuel).'
    )]
    #[ORM\Column(length: 15, nullable: true)]
    private ?string $phoneNumber = null;

    #[ORM\Column]
    private ?bool $admin = null;

    #[ORM\Column]
    private ?bool $active = null;

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
    #[Assert\NotNull(message: 'Le campus est obligatoire.')]
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
