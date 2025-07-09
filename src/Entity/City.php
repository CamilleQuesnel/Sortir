<?php

namespace App\Entity;

use App\Repository\CityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CityRepository::class)]
class City
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotNull(message: 'Merci de remplir le nom de la ville.')]
    #[Assert\Length(max: 100, maxMessage: 'La longueur ne peut pas exéder {{ limit }} caractères.')]
    #[Assert\Length(min:1, minMessage: 'La ville doit comporter au moins {{ limit }} caractères.')]
    private ?string $name = null;

    #[ORM\Column(length: 10)]
    #[Assert\NotNull(message: 'Merci de remplir le code postal.')]
    #[Assert\Length(max: 10, maxMessage: 'La longueur ne peut pas exéder {{ limit }} caractères .')]
    #[Assert\Length(min:1, minMessage: 'Le code postal doit faire au moins {{ limit }} caractères.')]
    private ?string $zipCode = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = true;

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }


    /**
     * @var Collection<int, Spot>
     */
    #[ORM\OneToMany(targetEntity: Spot::class, mappedBy: 'city')]
    private Collection $spot;

    public function __construct()
    {
        $this->spot = new ArrayCollection();
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

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(string $zipCode): static
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    /**
     * @return Collection<int, Spot>
     */
    public function getSpot(): Collection
    {
        return $this->spot;
    }

    public function addSpot(Spot $spot): static
    {
        if (!$this->spot->contains($spot)) {
            $this->spot->add($spot);
            $spot->setCity($this);
        }

        return $this;
    }

    public function removeSpot(Spot $spot): static
    {
        if ($this->spot->removeElement($spot)) {
            // set the owning side to null (unless already changed)
            if ($spot->getCity() === $this) {
                $spot->setCity(null);
            }
        }

        return $this;
    }
}
