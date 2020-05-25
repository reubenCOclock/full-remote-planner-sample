<?php

namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProjectDaysRepository")
 */
class ProjectDays
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Project", inversedBy="projectDays")
     */
    private $project;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MonthlySummary", inversedBy="projectDays")
     */
    private $monthlySummary;

    /**
     * @ORM\Column(type="integer", nullable=true)
     *  @Assert\Choice(choices={1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23}, message="Saisis un chiffre entre 0 et 23")
     */
    private $days;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getMonthlySummary(): ?MonthlySummary
    {
        return $this->monthlySummary;
    }

    public function setMonthlySummary(?MonthlySummary $monthlySummary): self
    {
        $this->monthlySummary = $monthlySummary;

        return $this;
    }

    public function getDays(): ?int
    {
        return $this->days;
    }

    public function setDays(?int $days): self
    {
        $this->days = $days;

        return $this;
    }
}
