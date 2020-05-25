<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SubVacationRepository")
 */
class SubVacation
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $typeOfVacation;

    /**
     * @ORM\Column(type="datetime")
     */
    private $startDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private $endDate;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Vacation", inversedBy="subVacations")
     */
    private $vacation;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="subVacations")
     */
    private $consultant;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $svAbsenceDays;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

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

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getVacation(): ?Vacation
    {
        return $this->vacation;
    }

    public function setVacation(?Vacation $vacation): self
    {
        $this->vacation = $vacation;

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

    public function getSvAbsenceDays(): ?int
    {
        return $this->svAbsenceDays;
    }

    public function setSvAbsenceDays(?int $svAbsenceDays): self
    {
        $this->svAbsenceDays = $svAbsenceDays;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
