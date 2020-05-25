<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\VacationRepository")
 */
class Vacation
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $typeOfVacation;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $startDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private $endDate;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isValidated;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="consultantVacations")
     */
    private $consultant;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="validatedVacations")
     */
    private $rhValidator;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SubVacation", mappedBy="vacation",cascade={"persist"})
     */
    private $subVacations;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $grantedAbsenceDays; 

    public function __construct(){
        $this->setIsValidated(false);
        $this->setCreatedAt(new \DateTime());
        $this->subVacations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeOfVacation(): ?string
    {
        return $this->typeOfVacation;
    }

    public function setTypeOfVacation(string $typeOfVacation): self
    {
        $this->typeOfVacation = $typeOfVacation;

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

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getIsValidated(): ?bool
    {
        return $this->isValidated;
    }

    public function setIsValidated(bool $isValidated): self
    {
        $this->isValidated = $isValidated;

        return $this;
    }

    public function getConsultant(): ?User
    {
        return $this->consultant;
    }

    public function setConsultant(?User $consultant): self
    {
        $this->consultant = $consultant;

        return $this;
    }

    public function getRhValidator(): ?User
    {
        return $this->rhValidator;
    }

    public function setRhValidator(?User $rhValidator): self
    {
        $this->rhValidator = $rhValidator;

        return $this;
    }

    /**
     * @return Collection|SubVacation[]
     */
    public function getSubVacations(): Collection
    {
        return $this->subVacations;
    }

    public function addSubVacation(SubVacation $subVacation): self
    {
        if (!$this->subVacations->contains($subVacation)) {
            $this->subVacations[] = $subVacation;
            $subVacation->setVacation($this);
        }

        return $this;
    }

    public function removeSubVacation(SubVacation $subVacation): self
    {
        if ($this->subVacations->contains($subVacation)) {
            $this->subVacations->removeElement($subVacation);
            // set the owning side to null (unless already changed)
            if ($subVacation->getVacation() === $this) {
                $subVacation->setVacation(null);
            }
        }

        return $this;
    }

    public function getGrantedAbsenceDays(): ?int
    {
        return $this->grantedAbsenceDays;
    }

    public function setGrantedAbsenceDays(?int $grantedAbsenceDays): self
    {
        $this->grantedAbsenceDays = $grantedAbsenceDays;

        return $this;
    }
}
