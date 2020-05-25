<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity(repositoryClass="App\Repository\FormationRepository")
 */
class Formation
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
    private $title;
    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;
    /**
     * @ORM\Column(type="datetime")
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
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="consultantFormations")
     */
    private $consultant;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="validatedFormations")
     */
    private $rhValidator;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $grantedAbsenceDays; 
    
    public function __construct(){
        $this->setisValidated(false);
        $this->setCreatedAt(new \DateTime());
        $this->setUpdatedAt(new \DateTime());
    }
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getTitle(): ?string
    {
        return $this->title;
    }
    public function setTitle(string $title): self
    {
        $this->title = $title;
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
    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
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