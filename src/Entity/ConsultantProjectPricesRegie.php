<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ConsultantProjectPricesRegieRepository")
 */
class ConsultantProjectPricesRegie
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $price;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Project", inversedBy="consultantProjectPricesRegies")
     */
    private $project;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\user", inversedBy="consultantProjectPricesRegies")
     */
    private $consultant;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $consultantFirstName;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(?int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): self
    {
        $this->project = $project;

        return $this;
    }

    public function getConsultant(): ?user
    {
        return $this->consultant;
    }

    public function setConsultant(?user $consultant): self
    {
        $this->consultant = $consultant;

        return $this;
    }

    public function getConsultantFirstName(): ?string
    {
        return $this->consultantFirstName;
    }

    public function setConsultantFirstName(?string $consultantFirstName): self
    {
        $this->consultantFirstName = $consultantFirstName;

        return $this;
    }
}
