<?php
namespace App\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity(repositoryClass="App\Repository\MyDocumentRepository")
 */
class MyDocument
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
    private $url;
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $category;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="clientDocuments")
     */
    private $client;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="rhDocuments")
     */
    private $rh;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="consultantDocuments")
     */
    private $consultant;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="managerDocuments")
     */
    private $manager;
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Project", inversedBy="projectDocuments")
     */
    private $project;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $month;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $days;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $year;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $contractType;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $consultantRate;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SickDay", mappedBy="document")
     */
    private $sickDays;

  

    public function __construct(){
        $this->setCategory('facturation');
        $this->setUrl('bidon.fr');
        $this->setTitle('titre');
        $this->setCreatedAt( new \DateTime());
        $this->sickDays = new ArrayCollection();
    }

    public function getId(): ?int
    {
        
        return $this->id;
    }
    public function getUrl(): ?string
    {
        return $this->url;
    }
    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }
    public function getCategory(): ?string
    {
        return $this->category;
    }
    public function setCategory(string $category): self
    {
        $this->category = $category;
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
    public function getRh(): ?User
    {
        return $this->rh;
    }
    public function setRh(?User $rh): self
    {
        $this->rh = $rh;
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
    public function getManager(): ?User
    {
        return $this->manager;
    }
    public function setManager(?User $manager): self
    {
        $this->manager = $manager;
        return $this;
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

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): self
    {
        $this->project = $project;

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

    public function getMonth(): ?int
    {
        return $this->month;
    }

    public function setMonth(?int $month): self
    {
        $this->month = $month;

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

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(?int $year): self
    {
        $this->year = $year;

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

    public function getConsultantRate(): ?int
    {
        return $this->consultantRate;
    }

    public function setConsultantRate(?int $consultantRate): self
    {
        $this->consultantRate = $consultantRate;

        return $this;
    }

    /**
     * @return Collection|SickDay[]
     */
    public function getSickDays(): Collection
    {
        return $this->sickDays;
    }

    public function addSickDay(SickDay $sickDay): self
    {
        if (!$this->sickDays->contains($sickDay)) {
            $this->sickDays[] = $sickDay;
            $sickDay->setDocument($this);
        }

        return $this;
    }

    public function removeSickDay(SickDay $sickDay): self
    {
        if ($this->sickDays->contains($sickDay)) {
            $this->sickDays->removeElement($sickDay);
            // set the owning side to null (unless already changed)
            if ($sickDay->getDocument() === $this) {
                $sickDay->setDocument(null);
            }
        }

        return $this;
    }

  
}