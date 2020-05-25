<?php

namespace App\Entity;
use DateTimeInterface;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\Common\Collections\ArrayCollection;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;


/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks
 * @Vich\Uploadable
 */
class User implements UserInterface, \Serializable
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
     * 
     * @var \DateTime
     */
    private $updatedAt;
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $beginningDate;
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $endDate;
    /**
     * @ORM\Column(type="string", length=64)
     */
    private $lastname;
    /**
     * @ORM\Column(type="string", length=64)
     */
    private $firstname;
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $birthday;
   /**
    * @ORM\Column(type="string",length=30,nullable=true)
    */
    private $ssId;
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $probationPeriod;
    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $contractualStatus;
    /**
     * @ORM\Column(type="string", length=64)
     */
    private $email;
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;
   
    /**
     * @ORM\Column(type="boolean")
     */
    private $isEmployed;
    /**
     * @ORM\Column(type="string", length=20 , nullable=true)
     */
    private $phoneNumber;
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $adress;
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Project", mappedBy="client")
     */
    private $clientProjects;
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Project", mappedBy="manager")
     */
    private $managerProjects;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MyDocument", mappedBy="client")
     */
    private $clientDocuments;
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MyDocument", mappedBy="rh")
     */
    private $rhDocuments;
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MyDocument", mappedBy="consultant")
     */
    private $consultantDocuments;
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MyDocument", mappedBy="manager")
     */
    private $managerDocuments;
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MonthlySummary", mappedBy="consultant")
     */
    private $consultantMonthlySummaries;
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SickDay", mappedBy="consultant")
     */
    private $consultantSickDays;
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Vacation", mappedBy="consultant")
     */
    private $consultantVacations;
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Vacation", mappedBy="rhValidator")
     */
    private $validatedVacations;
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Formation", mappedBy="consultant")
     */
    private $consultantFormations;
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Formation", mappedBy="rhValidator")
     */
    private $validatedFormations;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Role", inversedBy="users")
     */
    private $role;
    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $slug;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Feedback", mappedBy="consultant")
     */
    private $consultantFeedbacks;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $companyName; 
    
    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     * 
     * @Vich\UploadableField(mapping="avatar", fileNameProperty="imageName")
     * 
     * @var File
     */
    private $imageFile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @var string
     */
    private $imageName;

  /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Project", mappedBy="consultants")
     */
    private $consultantProjects;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SubVacation", mappedBy="consultant")
     */
    private $subVacations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ConsultantProjectPricesRegie", mappedBy="consultant")
     */
    private $consultantProjectPricesRegies;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\FilterConge", mappedBy="consultant")
     */
    private $filterConges;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $adresseCodePostal;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isHashed;

   
    
    public function __construct()
    { 
        $this->setIsEmployed(true);
        $this->setCreatedAt(new \DateTime());
        //$this->setEndDate(new \DateTime());
        $this->setUpdatedAt(new \DateTime());
        $this->projects = new ArrayCollection();
        $this->managerProjects = new ArrayCollection();
        $this->clientDocuments = new ArrayCollection();
        $this->rhDocuments = new ArrayCollection();
        $this->consultantDocuments = new ArrayCollection();
        $this->managerDocuments = new ArrayCollection();
        $this->consultantMonthlySummaries = new ArrayCollection();
        $this->consultantSickDays = new ArrayCollection();
        $this->consultantVacations = new ArrayCollection();
        $this->validatedVacations = new ArrayCollection();
        $this->consultantFormations = new ArrayCollection();
        $this->validatedFormations = new ArrayCollection();
        $this->consultantFeedbacks = new ArrayCollection();
        $this->clientProjects=new ArrayCollection();
        $this->consultantProjects=new ArrayCollection();
        $this->subVacations = new ArrayCollection();
        $this->consultantProjectPricesRegies = new ArrayCollection();
        $this->filterConges = new ArrayCollection();
        
    } 
    

    /**
     * Permet d'initialiser le slug
    * 
    * @ORM\PrePersist
    * @ORM\PreUpdate
    * 
    * @return void
    * 
    */
    public function initializeSlug(){
        if(empty($this->slug)) {
            $slugify = new Slugify();
            $this->slug=$slugify->slugify($this->lastname);
        }
    }
    
    // /**
    //  * Permet d'initialiser le hash du mot de passe
    // * 
    // * @ORM\PrePersist
    // * @ORM\PreUpdate
    // * 
    // * @return void
    // * 
    // */
    // public function hashPassword(UserPasswordEncoderInterface $hash){
    //     $hash = new UserInterface();
    //     $this->password = $hash->encodePassword($this,$this->getPassword());
    //     $this->setPassword($this->password);
    // }
    
    public function getSalt(){
        return null;
    } 
    public function getUserName(){
        return null;
    } 
    public function eraseCredentials(){
        return null;
    } 
    public function unserialize($serialized)
       {
           list (
               $this->id,
               $this->firstname,
               $this->password,
               // see section on salt below
               // $this->salt
           ) = unserialize($serialized, ['allowed_classes' => false]);
       } 
       public function serialize()
       {
           return serialize([
               $this->id,
               $this->firstname,
               $this->password,
               // see section on salt below
               // $this->salt,
           ]);
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
    public function getBeginningDate(): ?\DateTimeInterface
    {
        return $this->beginningDate;
    }
    public function setBeginningDate(?\DateTimeInterface $beginningDate): self
    {
        $this->beginningDate = $beginningDate;
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
    public function getLastname(): ?string
    {
        return $this->lastname;
    }
    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;
        return $this;
    }
    public function getFirstname(): ?string
    {
        return $this->firstname;
    }
    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;
        return $this;
    }
    public function getBirthday(): ?\DateTimeInterface
    {
        return $this->birthday;
    }
    public function setBirthday(?\DateTimeInterface $birthday): self
    {
        $this->birthday = $birthday;
        return $this;
    }
    public function getSsId(): ?string
    {
        return $this->ssId;
    }
    public function setSsId(?string $ssId): self
    {
        $this->ssId = $ssId;
        return $this;
    }
    public function getProbationPeriod(): ?\DateTimeInterface
    {
        return $this->probationPeriod;
    }
    public function setProbationPeriod(?\DateTimeInterface $probationPeriod): self
    {
        $this->probationPeriod = $probationPeriod;
        return $this;
    }
    public function getContractualStatus(): ?string
    {
        return $this->contractualStatus;
    }
    public function setContractualStatus(?string $contractualStatus): self
    {
        $this->contractualStatus = $contractualStatus;
        return $this;
    }
    public function getEmail(): ?string
    {
        return $this->email;
    }
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }
    public function getPassword(): ?string
    {
        return $this->password;
    }
    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }
    public function getIsEmployed(): ?bool
    {
        return $this->isEmployed;
    }
    public function setIsEmployed(bool $isEmployed): self
    {
        $this->isEmployed = $isEmployed;
        return $this;
    }
    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }
    public function setPhoneNumber(?string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }
    public function getAdress(): ?string
    {
        return $this->adress;
    }
    public function setAdress(?string $adress): self
    {
        $this->adress = $adress;
        return $this;
    }
    /**
     * @return Collection|Project[]string
     */
    public function getClientProjects(): Collection
    {
        return $this->clientProjects;
    }
    public function addClientProject(Project $project): self
    {
        if (!$this->clientProjects->contains($project)) {
            $this->clientProjects[] = $project;
            $project->setClient($this);
        }
        return $this;
    }
    public function removeClientProject(Project $project): self
    {
        if ($this->clientProjects->contains($project)) {
            $this->clientProjects->removeElement($project);
            // set the owning side to null (unless already changed)
            if ($project->getClient() === $this) {
                $project->setClient(null);
            }
        }
        return $this;
    }
    /**
     * @return Collection|Project[]
     */
    public function getManagerProjects(): Collection
    {
        return $this->managerProjects;
    }
    public function addManagerProject(Project $managerProject): self
    {
        if (!$this->managerProjects->contains($managerProject)) {
            $this->managerProjects[] = $managerProject;
            $managerProject->setManager($this);
        }
        return $this;
    }
    public function removeManagerProject(Project $managerProject): self
    {
        if ($this->managerProjects->contains($managerProject)) {
            $this->managerProjects->removeElement($managerProject);
            // set the owning side to null (unless already changed)
            if ($managerProject->getManager() === $this) {
                $managerProject->setManager(null);
            }
        }
        return $this;
    }
   
  
    /**
     * @return Collection|MyDocument[]
     */
    public function getClientDocuments(): Collection
    {
        return $this->clientDocuments;
    }
    public function addClientDocument(MyDocument $clientDocument): self
    {
        if (!$this->clientDocuments->contains($clientDocument)) {
            $this->clientDocuments[] = $clientDocument;
            $clientDocument->setClientDoc($this);
        }
        return $this;
    }
    public function removeClientDocument(MyDocument $clientDocument): self
    {
        if ($this->clientDocuments->contains($clientDocument)) {
            $this->clientDocuments->removeElement($clientDocument);
            // set the owning side to null (unless already changed)
            if ($clientDocument->getClientDoc() === $this) {
                $clientDocument->setClientDoc(null);
            }
        }
        return $this;
    }
    /**
     * @return Collection|MyDocument[]
     */
    public function getRhDocuments(): Collection
    {
        return $this->rhDocuments;
    }
    public function addRhDocument(MyDocument $rhDocument): self
    {
        if (!$this->rhDocuments->contains($rhDocument)) {
            $this->rhDocuments[] = $rhDocument;
            $rhDocument->setRhDoc($this);
        }
        return $this;
    }
    public function removeRhDocument(MyDocument $rhDocument): self
    {
        if ($this->rhDocuments->contains($rhDocument)) {
            $this->rhDocuments->removeElement($rhDocument);
            // set the owning side to null (unless already changed)
            if ($rhDocument->getRhDoc() === $this) {
                $rhDocument->setRhDoc(null);
            }
        }
        return $this;
    }
    /**
     * @return Collection|MyDocument[]
     */
    public function getConsultantDocuments(): Collection
    {
        return $this->consultantDocuments;
    }
    public function addConsultantDocument(MyDocument $consultantDocument): self
    {
        if (!$this->consultantDocuments->contains($consultantDocument)) {
            $this->consultantDocuments[] = $consultantDocument;
            $consultantDocument->setConsultantDoc($this);
        }
        return $this;
    }
    public function removeConsultantDocument(MyDocument $consultantDocument): self
    {
        if ($this->consultantDocuments->contains($consultantDocument)) {
            $this->consultantDocuments->removeElement($consultantDocument);
            // set the owning side to null (unless already changed)
            if ($consultantDocument->getConsultantDoc() === $this) {
                $consultantDocument->setConsultantDoc(null);
            }
        }
        return $this;
    }
    /**
     * @return Collection|MyDocument[]
     */
    public function getManagerDocuments(): Collection
    {
        return $this->managerDocuments;
    }
    public function addManagerDocument(MyDocument $managerDocument): self
    {
        if (!$this->managerDocuments->contains($managerDocument)) {
            $this->managerDocuments[] = $managerDocument;
            $managerDocument->setManagerDoc($this);
        }
        return $this;
    }
    public function removeManagerDocument(MyDocument $managerDocument): self
    {
        if ($this->managerDocuments->contains($managerDocument)) {
            $this->managerDocuments->removeElement($managerDocument);
            // set the owning side to null (unless already changed)
            if ($managerDocument->getManagerDoc() === $this) {
                $managerDocument->setManagerDoc(null);
            }
        }
        return $this;
    }
    /**
     * @return Collection|MonthlySummary[]
     */
    public function getConsultantMonthlySummaries(): Collection
    {
        return $this->consultantMonthlySummaries;
    }
    public function addConsultantMonthlySummary(MonthlySummary $consultantMonthlySummary): self
    {
        if (!$this->consultantMonthlySummaries->contains($consultantMonthlySummary)) {
            $this->consultantMonthlySummaries[] = $consultantMonthlySummary;
            $consultantMonthlySummary->setConsultant($this);
        }
        return $this;
    }
    public function removeConsultantMonthlySummary(MonthlySummary $consultantMonthlySummary): self
    {
        if ($this->consultantMonthlySummaries->contains($consultantMonthlySummary)) {
            $this->consultantMonthlySummaries->removeElement($consultantMonthlySummary);
            // set the owning side to null (unless already changed)
            if ($consultantMonthlySummary->getConsultant() === $this) {
                $consultantMonthlySummary->setConsultant(null);
            }
        }
        return $this;
    }
    /**
     * @return Collection|SickDay[]
     */
    public function getConsultantSickDays(): Collection
    {
        return $this->consultantSickDays;
    }
    public function addConsultantSickDay(SickDay $consultantSickDay): self
    {
        if (!$this->consultantSickDays->contains($consultantSickDay)) {
            $this->consultantSickDays[] = $consultantSickDay;
            $consultantSickDay->setConsultant($this);
        }
        return $this;
    }
    public function removeConsultantSickDay(SickDay $consultantSickDay): self
    {
        if ($this->consultantSickDays->contains($consultantSickDay)) {
            $this->consultantSickDays->removeElement($consultantSickDay);
            // set the owning side to null (unless already changed)
            if ($consultantSickDay->getConsultant() === $this) {
                $consultantSickDay->setConsultant(null);
            }
        }
        return $this;
    }
    /**
     * @return Collection|Vacation[]
     */
    public function getConsultantVacations(): Collection
    {
        return $this->consultantVacations;
    }
    public function addConsultantVacation(Vacation $consultantVacation): self
    {
        if (!$this->consultantVacations->contains($consultantVacation)) {
            $this->consultantVacations[] = $consultantVacation;
            $consultantVacation->setConsultant($this);
        }
        return $this;
    }
    public function removeConsultantVacation(Vacation $consultantVacation): self
    {
        if ($this->consultantVacations->contains($consultantVacation)) {
            $this->consultantVacations->removeElement($consultantVacation);
            // set the owning side to null (unless already changed)
            if ($consultantVacation->getConsultant() === $this) {
                $consultantVacation->setConsultant(null);
            }
        }
        return $this;
    }
    /**
     * @return Collection|Vacation[]
     */
    public function getValidatedVacations(): Collection
    {
        return $this->validatedVacations;
    }
    public function addValidatedVacation(Vacation $validatedVacation): self
    {
        if (!$this->validatedVacations->contains($validatedVacation)) {
            $this->validatedVacations[] = $validatedVacation;
            $validatedVacation->setRhValidator($this);
        }
        return $this;
    }
    public function removeValidatedVacation(Vacation $validatedVacation): self
    {
        if ($this->validatedVacations->contains($validatedVacation)) {
            $this->validatedVacations->removeElement($validatedVacation);
            // set the owning side to null (unless already changed)
            if ($validatedVacation->getRhValidator() === $this) {
                $validatedVacation->setRhValidator(null);
            }
        }
        return $this;
    }
    /**
     * @return Collection|Formation[]
     */
    public function getConsultantFormations(): Collection
    {
        return $this->consultantFormations;
    }
    public function addConsultantFormation(Formation $consultantFormation): self
    {
        if (!$this->consultantFormations->contains($consultantFormation)) {
            $this->consultantFormations[] = $consultantFormation;
            $consultantFormation->setConsultant($this);
        }
        return $this;
    }
    public function removeConsultantFormation(Formation $consultantFormation): self
    {
        if ($this->consultantFormations->contains($consultantFormation)) {
            $this->consultantFormations->removeElement($consultantFormation);
            // set the owning side to null (unless already changed)
            if ($consultantFormation->getConsultant() === $this) {
                $consultantFormation->setConsultant(null);
            }
        }
        return $this;
    }
    /**
     * @return Collection|Formation[]
     */
    public function getValidatedFormations(): Collection
    {
        return $this->validatedFormations;
    }
    public function addValidatedFormation(Formation $validatedFormation): self
    {
        if (!$this->validatedFormations->contains($validatedFormation)) {
            $this->validatedFormations[] = $validatedFormation;
            $validatedFormation->setRhValidator($this);
        }
        return $this;
    }
    public function removeValidatedFormation(Formation $validatedFormation): self
    {
        if ($this->validatedFormations->contains($validatedFormation)) {
            $this->validatedFormations->removeElement($validatedFormation);
            // set the owning side to null (unless already changed)
            if ($validatedFormation->getRhValidator() === $this) {
                $validatedFormation->setRhValidator(null);
            }
        }
        return $this;
    } 
    public function getRoles(){
        return [$this->getRole()->getRoleTitle()];
    }
    public function getRole(): ?Role
    {
        return $this->role;
    }
    public function setRole(?Role $role): self
    {
        $this->role = $role;
        return $this;
    }
    public function getSlug(): ?string
    {
        return $this->slug;
    }
    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }
  
    /**
     * @return Collection|Feedback[]
     */
    public function getConsultantFeedbacks(): Collection
    {
        return $this->consultantFeedbacks;
    }
    public function addConsultantFeedback(Feedback $consultantFeedback): self
    {
        if (!$this->consultantFeedbacks->contains($consultantFeedback)) {
            $this->consultantFeedbacks[] = $consultantFeedback;
            $consultantFeedback->setConsultant($this);
        }
        return $this;
    }
    public function removeConsultantFeedback(Feedback $consultantFeedback): self
    {
        if ($this->consultantFeedbacks->contains($consultantFeedback)) {
            $this->consultantFeedbacks->removeElement($consultantFeedback);
            // set the owning side to null (unless already changed)
            if ($consultantFeedback->getConsultant() === $this) {
                $consultantFeedback->setConsultant(null);
            }
        }
        return $this;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(?string $companyName): self
    {
        $this->companyName = $companyName;

        return $this;
    } 

    

      



    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile $imageFile
     */
    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTime('now');
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

        /**
     * @return Collection|Project[]
     */
    public function getConsultantProjects(): Collection
    {
        return $this->consultantProjects;
    }
    public function addConsultantProject(Project $consultantProject): self
    {
        if (!$this->consultantProjects->contains($consultantProject)) {
            $this->consultantProjects[] = $consultantProject;
            $consultantProject->addConsultant($this);
        }
        return $this;
    }
    public function removeConsultantProject(Project $consultantProject): self
    {
        if ($this->consultantProjects->contains($consultantProject)) {
            $this->consultantProjects->removeElement($consultantProject);
            $consultantProject->removeConsultant($this);
        }
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
            $subVacation->setConsultant($this);
        }

        return $this;
    }

    public function removeSubVacation(SubVacation $subVacation): self
    {
        if ($this->subVacations->contains($subVacation)) {
            $this->subVacations->removeElement($subVacation);
            // set the owning side to null (unless already changed)
            if ($subVacation->getConsultant() === $this) {
                $subVacation->setConsultant(null);
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
            $consultantProjectPricesRegy->setConsultant($this);
        }

        return $this;
    }

    public function removeConsultantProjectPricesRegy(ConsultantProjectPricesRegie $consultantProjectPricesRegy): self
    {
        if ($this->consultantProjectPricesRegies->contains($consultantProjectPricesRegy)) {
            $this->consultantProjectPricesRegies->removeElement($consultantProjectPricesRegy);
            // set the owning side to null (unless already changed)
            if ($consultantProjectPricesRegy->getConsultant() === $this) {
                $consultantProjectPricesRegy->setConsultant(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|FilterConge[]
     */
    public function getFilterConges(): Collection
    {
        return $this->filterConges;
    }

    public function addFilterConge(FilterConge $filterConge): self
    {
        if (!$this->filterConges->contains($filterConge)) {
            $this->filterConges[] = $filterConge;
            $filterConge->setConsultant($this);
        }

        return $this;
    }

    public function removeFilterConge(FilterConge $filterConge): self
    {
        if ($this->filterConges->contains($filterConge)) {
            $this->filterConges->removeElement($filterConge);
            // set the owning side to null (unless already changed)
            if ($filterConge->getConsultant() === $this) {
                $filterConge->setConsultant(null);
            }
        }

        return $this;
    }

    public function getAdresseCodePostal(): ?string
    {
        return $this->adresseCodePostal;
    }

    public function setAdresseCodePostal(?string $adresseCodePostal): self
    {
        $this->adresseCodePostal = $adresseCodePostal;

        return $this;
    }

    public function getIsHashed(): ?bool
    {
        return $this->isHashed;
    }

    public function setIsHashed(?bool $isHashed): self
    {
        $this->isHashed = $isHashed;

        return $this;
    }

}