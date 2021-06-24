<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\GerenciaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

//behaviors
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;


use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ApiResource()
 * @Gedmo\Loggable 
 * @ORM\Entity(repositoryClass=GerenciaRepository::class)
 * @UniqueEntity(
 *     fields={"nombre"}, 
 *     message="Este nombre ya esta siendo usado por otra entidad."
 * )
 */

class Gerencia
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
     * @ORM\OneToMany(targetEntity=Perfil::class, mappedBy="gerencia", cascade={"persist", "remove"})
     */
    private $perfils;

    /**
     * @ORM\OneToMany(targetEntity=AdjuntoPago::class, mappedBy="gerencia")
     */
    private $adjuntoPagos;

    /**
     * @ORM\OneToMany(targetEntity=MetodoPago::class, mappedBy="gerencia")
     */
    private $metodoPagos;


    public function __construct()
    {
        $this->perfils = new ArrayCollection();
        $this->adjuntoPagos = new ArrayCollection();
        $this->metodoPagos = new ArrayCollection();
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
        $this->nombre = strtoupper($nombre);

        return $this;
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
            $perfil->setGerencia($this);
        }

        return $this;
    }

    public function removePerfil(Perfil $perfil): self
    {
        if ($this->perfils->removeElement($perfil)) {
            // set the owning side to null (unless already changed)
            if ($perfil->getGerencia() === $this) {
                $perfil->setGerencia(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|AdjuntoPago[]
     */
    public function getAdjuntoPagos(): Collection
    {
        return $this->adjuntoPagos;
    }

    public function addAdjuntoPago(AdjuntoPago $adjuntoPago): self
    {
        if (!$this->adjuntoPagos->contains($adjuntoPago)) {
            $this->adjuntoPagos[] = $adjuntoPago;
            $adjuntoPago->setGerencia($this);
        }

        return $this;
    }

    public function removeAdjuntoPago(AdjuntoPago $adjuntoPago): self
    {
        if ($this->adjuntoPagos->removeElement($adjuntoPago)) {
            // set the owning side to null (unless already changed)
            if ($adjuntoPago->getGerencia() === $this) {
                $adjuntoPago->setGerencia(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|MetodoPago[]
     */
    public function getMetodoPagos(): Collection
    {
        return $this->metodoPagos;
    }

    public function addMetodoPago(MetodoPago $metodoPago): self
    {
        if (!$this->metodoPagos->contains($metodoPago)) {
            $this->metodoPagos[] = $metodoPago;
            $metodoPago->setGerencia($this);
        }

        return $this;
    }

    public function removeMetodoPago(MetodoPago $metodoPago): self
    {
        if ($this->metodoPagos->removeElement($metodoPago)) {
            // set the owning side to null (unless already changed)
            if ($metodoPago->getGerencia() === $this) {
                $metodoPago->setGerencia(null);
            }
        }

        return $this;
    }


}
