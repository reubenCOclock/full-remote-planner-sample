<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\WorkingDaysTestRepository")
 */
class WorkingDaysTest
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
    private $year;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $month;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nbJourOuvre;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(?int $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getMonth(): ?int
    {
        return $this->month;
    }

    public function setMonth(?int $month): self
    {
        $this->month = $month;

        return $this;
    }

    public function getNbJourOuvre(): ?int
    {
        return $this->nbJourOuvre;
    }

    public function setNbJourOuvre(?int $nbJourOuvre): self
    {
        $this->nbJourOuvre = $nbJourOuvre;

        return $this;
    }
}
