<?php

namespace App\Entity;

use App\Repository\ApuestaTipoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;



/**
 * @ORM\Entity(repositoryClass=ApuestaTipoRepository::class)
 */
class ApuestaTipo
{


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
     * @ORM\Column(type="text", nullable=true)
     */
    private $descripcion;

    /**
     * @ORM\OneToMany(targetEntity=Apuesta::class, mappedBy="tipo")
     */
    private $apuestas;

    /**
     * @ORM\OneToMany(targetEntity=ApuestaPropuesta::class, mappedBy="tipo")
     */
    private $apuestaPropuestas;

    public function __construct()
    {
        $this->apuestas = new ArrayCollection();
        $this->apuestaPropuestas = new ArrayCollection();
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

    public function setDescripcion(?string $descripcion): self
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    /**
     * @return Collection|Apuesta[]
     */
    public function getApuestas(): Collection
    {
        return $this->apuestas;
    }

    public function addApuesta(Apuesta $apuesta): self
    {
        if (!$this->apuestas->contains($apuesta)) {
            $this->apuestas[] = $apuesta;
            $apuesta->setTipo($this);
        }

        return $this;
    }

    public function removeApuesta(Apuesta $apuesta): self
    {
        if ($this->apuestas->removeElement($apuesta)) {
            // set the owning side to null (unless already changed)
            if ($apuesta->getTipo() === $this) {
                $apuesta->setTipo(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ApuestaPropuesta[]
     */
    public function getApuestaPropuestas(): Collection
    {
        return $this->apuestaPropuestas;
    }

    public function addApuestaPropuesta(ApuestaPropuesta $apuestaPropuesta): self
    {
        if (!$this->apuestaPropuestas->contains($apuestaPropuesta)) {
            $this->apuestaPropuestas[] = $apuestaPropuesta;
            $apuestaPropuesta->setTipo($this);
        }

        return $this;
    }

    public function removeApuestaPropuesta(ApuestaPropuesta $apuestaPropuesta): self
    {
        if ($this->apuestaPropuestas->removeElement($apuestaPropuesta)) {
            // set the owning side to null (unless already changed)
            if ($apuestaPropuesta->getTipo() === $this) {
                $apuestaPropuesta->setTipo(null);
            }
        }

        return $this;
    }
}
