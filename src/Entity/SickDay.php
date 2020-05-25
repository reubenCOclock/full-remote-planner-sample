<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SickDayRepository")
 */
class SickDay
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

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
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="consultantSickDays")
     */
    private $consultant;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $grantedAbsenceDays;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MyDocument", inversedBy="sickDays")
     */
    private $document; 

    public function __construct(){
        $this->setCreatedAt(new \DateTime());
        $this->setUpdatedAt(new \DateTime());
        $this->setDescription('description arret malaide');
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

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

    public function getGrantedAbsenceDays(): ?int
    {
        return $this->grantedAbsenceDays;
    }

    public function setGrantedAbsenceDays(?int $grantedAbsenceDays): self
    {
        $this->grantedAbsenceDays = $grantedAbsenceDays;

        return $this;
    }

    public function getDocument(): ?MyDocument
    {
        return $this->document;
    }

    public function setDocument(?MyDocument $document): self
    {
        $this->document = $document;

        return $this;
    }
}
