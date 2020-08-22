<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TranslationRepository")
 * @UniqueEntity(fields={"identifier", "type"})
 * 
 */
class Translation
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
    private $translateEs;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $translateEn;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $translatePt;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=35, unique=true)
     */
    private $identifier;

    /**
     * @ORM\Column(name="isActive", type="boolean")
     */
    private $isActive;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $identificador;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->identificador = uniqid();
        $this->isActive = true;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTranslateEs(): ?string
    {
        return $this->translateEs;
    }

    public function setTranslateEs(string $translateEs): self
    {
        $this->translateEs = trim($translateEs);

        return $this;
    }

    public function getTranslateEn(): ?string
    {
        return $this->translateEn;
    }

    public function setTranslateEn(string $translateEn): self
    {
        $this->translateEn = trim($translateEn);

        return $this;
    }

    public function getTranslatePt(): ?string
    {
        return $this->translatePt;
    }

    public function setTranslatePt(string $translatePt): self
    {
        $this->translatePt = trim($translatePt);

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function setIsActive($isActive)
    {

        $this->isActive = $isActive;

        return $this;
    }

    public function getIsActive()
    {

        return $this->isActive;
    }

    public function getIdentificador(): ?string
    {
        return $this->identificador;
    }

    public function setIdentificador(string $identificador): self
    {
        $this->identificador = $identificador;

        return $this;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;

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
}
