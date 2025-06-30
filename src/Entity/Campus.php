<?php

namespace App\Entity;

use App\Repository\CampusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CampusRepository::class)]
class Campus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    /**
     * @var Collection<int, Hangout>
     */
    #[ORM\OneToMany(targetEntity: Hangout::class, mappedBy: 'campus', orphanRemoval: true)]
    private Collection $hangouts;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'campus')]
    private Collection $user;

    public function __construct()
    {
        $this->hangouts = new ArrayCollection();
        $this->user = new ArrayCollection();
    }

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
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Hangout>
     */
    public function getHangouts(): Collection
    {
        return $this->hangouts;
    }

    public function addHangout(Hangout $hangout): static
    {
        if (!$this->hangouts->contains($hangout)) {
            $this->hangouts->add($hangout);
            $hangout->setCampus($this);
        }

        return $this;
    }

    public function removeHangout(Hangout $hangout): static
    {
        if ($this->hangouts->removeElement($hangout)) {
            // set the owning side to null (unless already changed)
            if ($hangout->getCampus() === $this) {
                $hangout->setCampus(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUser(): Collection
    {
        return $this->user;
    }

    public function addUser(User $user): static
    {
        if (!$this->user->contains($user)) {
            $this->user->add($user);
            $user->setCampus($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->user->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getCampus() === $this) {
                $user->setCampus(null);
            }
        }

        return $this;
    }
}
