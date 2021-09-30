<?php

namespace App\Entity;

use App\Repository\PerfilBancoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;


//behaviors
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=PerfilBancoRepository::class)
 */
class PerfilBanco
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
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nombre;

    /**
     * @ORM\Column(type="text")
     */
    private $descripcion;

    /**
     * @ORM\Column(type="boolean")
     */
    private $activo;    

    /**
     * @ORM\ManyToOne(targetEntity=Perfil::class, inversedBy="perfilBancos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $perfil;

    /**
     * @ORM\OneToMany(targetEntity=RetiroSaldo::class, mappedBy="perfilBanco")
     */
    private $retiro_saldos;

    public function __construct()
    {
        $this->retiro_saldos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(string $descripcion): self
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getActivo(): ?bool
    {
        return $this->activo;
    }

    public function setActivo(bool $activo): self
    {
        $this->activo = $activo;

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

    /**
     * @return Collection|RetiroSaldo[]
     */
    public function getRetiroSaldos(): Collection
    {
        return $this->retiro_saldos;
    }

    public function addRetiroSaldo(RetiroSaldo $retiroSaldo): self
    {
        if (!$this->retiro_saldos->contains($retiroSaldo)) {
            $this->retiro_saldos[] = $retiroSaldo;
            $retiroSaldo->setPerfilBanco($this);
        }

        return $this;
    }

    public function removeRetiroSaldo(RetiroSaldo $retiroSaldo): self
    {
        if ($this->retiro_saldos->removeElement($retiroSaldo)) {
            // set the owning side to null (unless already changed)
            if ($retiroSaldo->getPerfilBanco() === $this) {
                $retiroSaldo->setPerfilBanco(null);
            }
        }

        return $this;
    }
}
