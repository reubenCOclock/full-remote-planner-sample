<?php
namespace App\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity(repositoryClass="App\Repository\ProjectRepository")
 */
class Project
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;
    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     */
    private $title;
    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $startDate;
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $endDate;
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="clientProjects")
     */
    private $client;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="managerProjects")
     */
    private $manager;
   
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Feedback", mappedBy="project")
     */ 

    private $projectFeedbacks;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isActive;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $price;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MyDocument", mappedBy="project")
     */
    private $projectDocuments;
/**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="consultantProjects")
     */
    private $consultants;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProjectDays", mappedBy="project")
     */
    private $projectDays;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $contractType;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProjectForfaitLivrables", mappedBy="project",cascade={"persist"})
     */
    private $projectForfaitLivrables;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ConsultantProjectPricesRegie", mappedBy="project",cascade={"persist"})
     */
    private $consultantProjectPricesRegies;

   
    

    public function __construct()
    { 
        $this->setCreatedAt(new \DateTime());
        $this->setIsActive(true);
        $this->consultants = new ArrayCollection();
        $this->projectFeedbacks= new ArrayCollection();
        $this->projectDocuments = new ArrayCollection();
       
        $this->projectDays = new ArrayCollection();
        $this->projectForfaitLivrables = new ArrayCollection();
        $this->consultantProjectPricesRegies = new ArrayCollection();
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
    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }
    public function setStartDate(?\DateTimeInterface $startDate): self
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
    public function getDescription(): ?string
    {
        return $this->description;
    }
    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }
    public function getClient(): ?User
    {
        return $this->client;
    }
    public function setClient(?User $client): self
    {
        $this->client = $client;
        return $this;
    }
    public function getManager(): ?User
    {
        return $this->manager;
    }
    public function setManager(?User $manager): self
    {
        $this->manager = $manager;
        return $this;
    }
   
    
    /**
     * @return Collection|Feedback[]
     */ 
     public function getProjectFeedbacks(): Collection 
     {
         return $this->projectFeedbacks;
     } 
     public function addProjectFeedback(Project $projectFeedback){ 
         if(!$this->projectFeedbacks->contains($projectFeedback)){
             $this->projectFeedbacks[]=$projectFeedback;
         }
     } 
     public function removeProjectFeedback(Project $projectFeedback){
        if($this->projectFeedbacks->contains($projectFeedback)){
            $this->projectFeedbacks->removeElement($projectFeedback);
        } 
        return $this;
     }

     public function getIsActive(): ?bool
     {
         return $this->isActive;
     }

     public function setIsActive(?bool $isActive): self
     {
         $this->isActive = $isActive;

         return $this;
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

     /**
      * @return Collection|MyDocument[]
      */
     public function getProjectDocuments(): Collection
     {
         return $this->projectDocuments;
     }

     public function addProjectDocument(MyDocument $projectDocument): self
     {
         if (!$this->projectDocuments->contains($projectDocument)) {
             $this->projectDocuments[] = $projectDocument;
             $projectDocument->setProject($this);
         }

         return $this;
     }

     public function removeProjectDocument(MyDocument $projectDocument): self
     {
         if ($this->projectDocuments->contains($projectDocument)) {
             $this->projectDocuments->removeElement($projectDocument);
             // set the owning side to null (unless already changed)
             if ($projectDocument->getProject() === $this) {
                 $projectDocument->setProject(null);
             }
         }

         return $this;
     }

      /**
     * @return Collection|User[]
     */
    public function getConsultants(): Collection
    {
        return $this->consultants;
    }
    public function addConsultant(User $consultant): self
    {
        if (!$this->consultants->contains($consultant)) {
            $this->consultants[] = $consultant;
        }
        return $this;
    }
    public function removeConsultant(User $consultant): self
    {
        if ($this->consultants->contains($consultant)) {
            $this->consultants->removeElement($consultant);
        }
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
            $projectDay->setProject($this);
        }

        return $this;
    }

    public function removeProjectDay(ProjectDays $projectDay): self
    {
        if ($this->projectDays->contains($projectDay)) {
            $this->projectDays->removeElement($projectDay);
            // set the owning side to null (unless already changed)
            if ($projectDay->getProject() === $this) {
                $projectDay->setProject(null);
            }
        }

        return $this;
    }

    public function getContractType(): ?string
    {
        return $this->contractType;
    }

    public function setContractType(?string $contractType): self
    {
        $this->contractType = $contractType;

        return $this;
    }

    /**
     * @return Collection|ProjectForfaitLivrables[]
     */
    public function getProjectForfaitLivrables(): Collection
    {
        return $this->projectForfaitLivrables;
    }

    public function addProjectForfaitLivrable(ProjectForfaitLivrables $projectForfaitLivrable): self
    {
        if (!$this->projectForfaitLivrables->contains($projectForfaitLivrable)) {
            $this->projectForfaitLivrables[] = $projectForfaitLivrable;
            $projectForfaitLivrable->setProject($this);
        }

        return $this;
    }

    public function removeProjectForfaitLivrable(ProjectForfaitLivrables $projectForfaitLivrable): self
    {
        if ($this->projectForfaitLivrables->contains($projectForfaitLivrable)) {
            $this->projectForfaitLivrables->removeElement($projectForfaitLivrable);
            // set the owning side to null (unless already changed)
            if ($projectForfaitLivrable->getProject() === $this) {
                $projectForfaitLivrable->setProject(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ConsultantProjectPricesRegie[]
     */
    public function getConsultantProjectPricesRegies(): Collection
    {
        return $this->consultantProjectPricesRegies;
    }

    public function addConsultantProjectPricesRegy(ConsultantProjectPricesRegie $consultantProjectPricesRegy): self
    {
        if (!$this->consultantProjectPricesRegies->contains($consultantProjectPricesRegy)) {
            $this->consultantProjectPricesRegies[] = $consultantProjectPricesRegy;
            $consultantProjectPricesRegy->setProject($this);
        }

        return $this;
    }

    public function removeConsultantProjectPricesRegy(ConsultantProjectPricesRegie $consultantProjectPricesRegy): self
    {
        if ($this->consultantProjectPricesRegies->contains($consultantProjectPricesRegy)) {
            $this->consultantProjectPricesRegies->removeElement($consultantProjectPricesRegy);
            // set the owning side to null (unless already changed)
            if ($consultantProjectPricesRegy->getProject() === $this) {
                $consultantProjectPricesRegy->setProject(null);
            }
        }

        return $this;
    }  
    
}