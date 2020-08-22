<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LinkRepository")
 */
class Link
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
     * @ORM\Column(type="string", length=80)
     */
    private $description;

    /**
     * @ORM\Column(type="integer")
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    private $icon;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $uri;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isGroup;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $identificador;

    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="Link")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fatherlink_id", referencedColumnName="id",onDelete="SET NULL")
     * })
     */
    private $fatherLink;

    /**
     * @var string
     *
     * @ORM\OneToMany(targetEntity="Link", mappedBy="fatherLink",cascade={"persist"})
     */
    private $children;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private $orden;

    /**
     *
     * @ORM\ManyToMany(targetEntity="Rol")
     * @ORM\JoinTable(name="link_role",
     *     joinColumns={@ORM\JoinColumn(name="link_id", referencedColumnName="id",onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="rol_id", referencedColumnName="id")}
     * )
     */
    protected $roleLinks;

    public function __construct()
    {
        $this->identificador = uniqid();
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
        $this->isGroup = false;
        $this->roleLinks = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function setRoleLinks($roleLinks)
    {
        if (!$this->roleLinks->contains($roleLinks))

            $this->roleLinks[] = $roleLinks;
        return $this;
    }

    public function getRoleLinks()
    {
        return $this->roleLinks;
    }

    public function removeRoleLinks(\App\Entity\Rol $roleLinks)
    {
        $this->roleLinks->removeElement($roleLinks);
    }

    public function getFatherLink()
    {
        return $this->fatherLink;
    }

    public function setFatherLink(\App\Entity\Link $fatherLink): self
    {
        $this->fatherLink = $fatherLink;

        return $this;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCode(): ?int
    {
        return $this->code;
    }

    public function setCode(int $code)
    {
        $this->code = $code;

        return $this;
    }

    public function getOrden(): ?int
    {
        return $this->orden;
    }

    public function setOrden(int $orden)
    {
        $this->orden = $orden;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function getUri(): ?string
    {
        return $this->uri;
    }

    public function setUri(?string $uri): self
    {
        $this->uri = $uri;

        return $this;
    }

    public function getIsGroup(): ?bool
    {
        return $this->isGroup;
    }

    public function setIsGroup(bool $isGroup): self
    {
        $this->isGroup = $isGroup;

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

    public function getIdentificador(): ?string
    {
        return $this->identificador;
    }

    public function setIdentificador(string $identificador): self
    {
        $this->identificador = $identificador;

        return $this;
    }
}
