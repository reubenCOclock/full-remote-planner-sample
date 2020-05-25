<?php

namespace App\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MonthlySummaryRepository")
 * @ORM\HasLifecycleCallbacks
 */

class MonthlySummary
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
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="consultantMonthlySummaries")
     */
    private $consultant;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $month;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProjectDays", mappedBy="monthlySummary",cascade={"persist"})
     */
    private $projectDays;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Choice(choices={1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23}, message="Saisis un chiffre entre 0 et 23")
     */
    private $totalDays;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $year;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $consultantAbsenceDays;



    public function __construct(){
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->projectDays = new ArrayCollection();
    }

    /**
    * Permet de mettre à jour le champ updatedAt
    * 
    * @ORM\PostUpdate
    * 
    * @return void
    * 
    */
    public function updateUpdatedAt(){
        if(!empty($this->updatedAt)){ 
            $this->setUpdatedAt(new \DateTime('now'));
        }
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

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

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

    public function getMonth(): ?int
    {
        return $this->month;
    }

    public function setMonth(?int $month): self
    {
        $this->month = $month;

        return $this;
    }

    /**
     * @return Collection|ProjectDays[]
     */
    public function getProjectDays(): Collection
    {
        return $this->projectDays;
    }

    public function addProjectDay(ProjectDays $projectDay): self
    {
        if (!$this->projectDays->contains($projectDay)) {
            $this->projectDays[] = $projectDay;
            $projectDay->setMonthlySummary($this);
        }

        return $this;
    }

    public function removeProjectDay(ProjectDays $projectDay): self
    {
        if ($this->projectDays->contains($projectDay)) {
            $this->projectDays->removeElement($projectDay);
            // set the owning side to null (unless already changed)
            if ($projectDay->getMonthlySummary() === $this) {
                $projectDay->setMonthlySummary(null);
            }
        }

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getTotalDays(): ?int
    {
        return $this->totalDays;
    }

    public function setTotalDays(?int $totalDays): self
    {
        $this->totalDays = $totalDays;

        return $this;
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

    public function getConsultantAbsenceDays(): ?int
    {
        return $this->consultantAbsenceDays;
    }

    public function setConsultantAbsenceDays(?int $consultantAbsenceDays): self
    {
        $this->consultantAbsenceDays = $consultantAbsenceDays;

        return $this;
    }

    
}
