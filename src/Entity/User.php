<?php

namespace App\Entity;

use App\Entity\Perfil;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Symfony\Component\Validator\Constraints as Assert;

use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

//behaviors
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;


/**
 * @ApiResource()
 * @Gedmo\Loggable 
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository") 
 * @UniqueEntity(
 *     fields={"email"}, 
 *     message="Este email ya esta siendo usado."
 * )
 */
class User implements UserInterface
{

    /**
     * Hook blameable behavior
     * updates createdBy, updatedBy fields
     */
    use BlameableEntity;
    /**
     * Hook timestampable behavior
     * updates createdAt, updatedAt fields
     */
    use TimestampableEntity;

    
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\Email(
     *     message = "El email '{{ value }}' no es valido."
     * )
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read"})
     */
    private $nombre;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read"})
     */

    private $apellido;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read"})
     */
    private $telefono;


    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $roles = [];    



    /**
     * @var string The hashed password
     * @ORM\Column(type="string", nullable=false)    
     * @Assert\Length(
     *      min = 7,
     *      max = 10,
     *      minMessage = "Se espera un valor minimo de {{ limit }} caracteres",
     *      maxMessage = "Se espera un valor minimo de {{ limit }} caracteres",
     *      allowEmptyString = true,
     *      groups={"edit"}
     * )
     * @Assert\Length(
     *      min = 3,
     *      max = 10,
     *      minMessage = "Se espera un valor minimo de {{ limit }} caracteres",
     *      maxMessage = "Se espera un valor minimo de {{ limit }} caracteres",
     *      allowEmptyString = false
     * )
     * @Assert\NotBlank(message="No debe estar vacio")
     */
    private $password;

    /**
     * @ORM\OneToMany(targetEntity=Perfil::class, mappedBy="usuario", cascade={"persist", "remove"})
     */
    private $perfils;

    /**
     * @ORM\ManyToOne(targetEntity=Perfil::class, cascade={"persist", "remove"})
     */
    private $perfil;




    public function __construct()
    {
        $this->perfils = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }


    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = strtoupper($nombre);

        return $this;
    }

    public function getApellido(): ?string
    {
        return $this->apellido;
    }

    public function setApellido(string $apellido): self
    {
        $this->apellido = strtoupper($apellido);

        return $this;
    }


    public function getTelefono(): ?string
    {
        return $this->telefono;
    }

    public function setTelefono(string $telefono): self
    {
        $this->telefono = $telefono;

        return $this;
    }    
    
    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection|Perfil[]
     */
    public function getPerfils(): Collection
    {
        return $this->perfils;
    }

    public function addPerfil(Perfil $perfil): self
    {
        if (!$this->perfils->contains($perfil)) {
            $this->perfils[] = $perfil;
            $perfil->setUsuario($this);
        }

        return $this;
    }

    public function removePerfil(Perfil $perfil): self
    {
        if ($this->perfils->removeElement($perfil)) {
            // set the owning side to null (unless already changed)
            if ($perfil->getUsuario() === $this) {
                $perfil->setUsuario(null);
            }
        }

        return $this;
    }

    public function getPerfil(): ?Perfil
    {
        return $this->perfil;
    }

    public function setPerfil(?Perfil $perfil): self
    {
        $this->perfil = $perfil;
        return $this;
        
    }



}
