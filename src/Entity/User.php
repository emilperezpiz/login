<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface, \Serializable
{

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="user", type="string", length=35, unique=true)
     */
    private $userName;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255)
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="salt", type="string", length=255, nullable=true)
     */
    private $salt;

    /**
     *
     * @ORM\ManyToMany(targetEntity="Rol")
     * @ORM\JoinTable(name="user_role",
     *     joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id",onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="rol_id", referencedColumnName="id")}
     * )
     */
    protected $userRoles;

    /**
     * @Assert\Image(maxSize = "5M",mimeTypes = {"image/jpg", "image/jpeg", "image/png"})
     */
    private $foto;

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=255,nullable=true)
     */
    private $path;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255,nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="surname", type="string", length=255,nullable=true)
     */
    private $surname;

    /**
     * @var string
     *
     * @ORM\Column(name="cpf", type="string", length=11,nullable=true)
     */
    private $cpf;

    /**
     * @ORM\Column(name="isActive", type="boolean")
     */
    private $isActive;

    /**
     * @ORM\Column(name="isLocked", type="boolean")
     */
    private $isLocked;

    /**
     * @ORM\Column(name="isDelete", type="boolean")
     */
    private $isDelete;

    /**
     * @ORM\Column(name="isMale", type="boolean")
     */
    private $isMale;

    /**
     * @ORM\Column(name="isFemale", type="boolean")
     */
    private $isFemale;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255,nullable=true)
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=25)
     */
    //private $country;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=18,nullable=true)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="confirmationCode", type="string",nullable=true)
     */
    private $confirmationCode;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="birtday", type="datetime", nullable=true)
     */
    private $birthday;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updatedAt", type="datetime")
     */
    private $updatedAt;

    /**
     * @var string
     *
     * @ORM\Column(name="identificador", type="string", length=255)
     */
    private $identificador;

    /**
     * @var string
     *
     * @ORM\Column(name="business", type="string", length=255, nullable=true)
     */
    private $business;

    //
    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->userName,
            $this->password,
            // see section on salt below
            // $this->salt,
        ));
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     */
    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->userName,
            $this->password,
            // see section on salt below
            // $this->salt
        ) = unserialize($serialized, array('allowed_classes' => false));
    }

    public function eraseCredentials()
    {
    }

    // BEGIN USER INTERFACE
    public function getRoles()
    {

        $role = array();
        foreach ($this->userRoles->toArray() as $value) {
            $role[] = $value->getRole();
        }
        //return ['ROLE_ADMIN'];
        //return $this->userRoles;
        //return $this->userRoles->toArray();
        return $role;
    }

    public function getUsername()
    {
        return $this->userName;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getSalt()
    {
        //return $this->salt;
        return null;
    }
    // END USER INTERFACE

    public function __construct()
    {
        $this->userRoles = new \Doctrine\Common\Collections\ArrayCollection();
        $this->isActive = false;
        $this->isLocked = false;
        $this->isFemale = false;
        $this->isMale = false;
        $this->updatedAt = new \DateTime();
        $this->createdAt = new \DateTime();
        $this->identificador = uniqid();
        $this->isDelete = false;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setUserName($userName)
    {

        $this->userName = $userName;

        return $this;
    }

    public function setPassword($password)
    {

        $this->password = $password;

        return $this;
    }

    public function setSalt($salt)
    {
        $this->salt = $salt;

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

    public function setIsDelete($isDelete)
    {
        $this->isDelete = $isDelete;

        return $this;
    }

    public function getDelete()
    {
        return $this->isDelete;
    }

    public function setIsLocked($isLocked)
    {
        $this->isLocked = $isLocked;

        return $this;
    }

    public function getIsLocked()
    {
        return $this->isLocked;
    }

    public function setUserRoles($roles)
    {
        if (!$this->userRoles->contains($roles))

            $this->userRoles[] = $roles;
        return $this;
    }

    public function getUserRoles()
    {
        return $this->userRoles;
    }

    // comprueba si el usuario cuenta con el rol recibido por parametro
    public function hasRole($role)
    {
        foreach ($this->userRoles->toArray() as $rol) {
            if ($rol->getRole() == $role) {

                return true;
            }
        }
        return false;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getCpf()
    {
        return $this->cpf;
    }

    public function setCpf($cpf)
    {
        $cpf = str_replace(".", "", $cpf);
        $cpf = str_replace("-", "", $cpf);
        $this->cpf = $cpf;

        return $this;
    }

    public function getSurName()
    {
        return $this->surname;
    }

    public function setSurName($surname)
    {
        $this->surname = $surname;

        return $this;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function setPhone($phone)
    {
        $phone = str_replace("(", "", $phone);
        $phone = str_replace(")", "", $phone);
        $phone = str_replace("-", "", $phone);
        $this->phone = $phone;
        return $this;
    }

    public function getConfirmationCode()
    {
        return $this->confirmationCode;
    }

    public function setConfirmationCode($confirmationCode)
    {
        $this->confirmationCode = $confirmationCode;

        return $this;
    }

    public function setIsMale($isMale)
    {
        $this->isMale = $isMale;

        return $this;
    }

    public function getIsMale()
    {
        return $this->isMale;
    }

    public function setIsFemale($isFemale)
    {
        $this->isFemale = $isFemale;

        return $this;
    }

    public function getIsFemale()
    {
        return $this->isFemale;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function setAddress($address)
    {
        $this->address = $address;
    }

    public function getBirthday()
    {
        return $this->birthday;
    }

    public function setBirthday(\DateTimeInterface $birtday): self
    {
        $this->birthday = $birtday;

        return $this;
    }

    /**
     * Set foto
     *
     * @param UploadedFile $foto
     *
     * @return perfilUsuario
     */
    public function setFoto(UploadedFile $foto = null)
    {
        $this->foto = $foto;

        return $this;
    }

    /**
     * Get foto
     *
     * @return UploadedFile
     */
    public function getFoto()
    {
        return $this->foto;
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

    public function setBusiness(string $business): self
    {
        $this->business = $business;

        return $this;
    }

    public function getBusiness(): ?string
    {
        return $this->business;
    }

    public function removeUserRoles(\App\Entity\Rol $userRoles)
    {
        $this->userRoles->removeElement($userRoles);
    }

    public function getCurrentRoles()
    {

        //return ['ROLE_ADMIN'];
        return $this->userRoles->toArray();
    }
}
