<?php

namespace App\Entity;

use App\Repository\CitiesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CitiesRepository::class)]
class Cities
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'string', length: 2, nullable: true)]
    private $country;

    #[ORM\Column(type: 'float')]
    private $lat;

    #[ORM\Column(type: 'float')]
    private $lon;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $pl;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $en;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'cities')]
    private $users;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $tempC;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $tempF;

    #[ORM\Column(type: 'string', length: 3, nullable: true)]
    private $icon;

    #[ORM\Column(type: 'boolean')]
    private $def;

    #[ORM\Column(type: 'boolean')]
    private $hasUser;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getLat(): ?float
    {
        return $this->lat;
    }

    public function setLat(float $lat): self
    {
        $this->lat = $lat;

        return $this;
    }

    public function getLon(): ?float
    {
        return $this->lon;
    }

    public function setLon(float $lon): self
    {
        $this->lon = $lon;

        return $this;
    }

    public function getPl(): ?string
    {
        return $this->pl;
    }

    public function setPl(?string $pl): self
    {
        $this->pl = $pl;

        return $this;
    }

    public function getEn(): ?string
    {
        return $this->en;
    }

    public function setEn(?string $en): self
    {
        $this->en = $en;

        return $this;
    }

    /**
     * @return Collection<int, user>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(user $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
        }

        return $this;
    }

    public function removeUser(user $user): self
    {
        $this->users->removeElement($user);

        return $this;
    }

    public function getTempC(): ?int
    {
        return $this->tempC;
    }

    public function setTempC(?int $tempC): self
    {
        $this->tempC = $tempC;

        return $this;
    }

    public function getTempF(): ?int
    {
        return $this->tempF;
    }

    public function setTempF(?int $tempF): self
    {
        $this->tempF = $tempF;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function isDef(): ?bool
    {
        return $this->def;
    }

    public function setDef(bool $def): self
    {
        $this->def = $def;

        return $this;
    }

    public function isHasUser(): ?bool
    {
        return $this->hasUser;
    }

    public function setHasUser(bool $hasUser): self
    {
        $this->hasUser = $hasUser;

        return $this;
    }
}
