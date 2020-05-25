<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity(repositoryClass="App\Repository\FeedbackRepository")
 */
class Feedback
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;
    /**
     * @ORM\Column(type="string", length=50)
     */
    private $title;
    /**
     * @ORM\Column(type="text")
     */
    private $description;
    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Project", inversedBy="projectFeedbacks")
     */
    private $project;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="consultantFeedbacks")
     */
    private $consultant; 

    public function __construct(){
        $this->setCreatedAt(new \DateTime());
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
    public function getDescription(): ?string
    {
        return $this->description;
    }
    public function setDescription(string $description): self
    {
        $this->description = $description;
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
    public function getProject(): ?Project
    {
        return $this->project;
    }
    public function setProject(?Project $project): self
    {
        $this->project = $project;
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
}