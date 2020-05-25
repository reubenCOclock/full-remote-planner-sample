<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LoadInitialDocumentsRepository")
 */
class LoadInitialDocuments
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cv;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $carte_identite;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $rib;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $navigo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $attestation_domicile;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCv(): ?string
    {
        return $this->cv;
    }

    public function setCv(?string $cv): self
    {
        $this->cv = $cv;

        return $this;
    }

    public function getCarteIdentite(): ?string
    {
        return $this->carte_identite;
    }

    public function setCarteIdentite(?string $carte_identite): self
    {
        $this->carte_identite = $carte_identite;

        return $this;
    }

    public function getRib(): ?string
    {
        return $this->rib;
    }

    public function setRib(?string $rib): self
    {
        $this->rib = $rib;

        return $this;
    }

    public function getNavigo(): ?string
    {
        return $this->navigo;
    }

    public function setNavigo(?string $navigo): self
    {
        $this->navigo = $navigo;

        return $this;
    }

    public function getAttestationDomicile(): ?string
    {
        return $this->attestation_domicile;
    }

    public function setAttestationDomicile(?string $attestation_domicile): self
    {
        $this->attestation_domicile = $attestation_domicile;

        return $this;
    }
}
