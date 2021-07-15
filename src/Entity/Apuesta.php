<?php

namespace App\Entity;

use App\Repository\ApuestaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ApuestaRepository::class)
 */
class Apuesta
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Carrera::class, inversedBy="apuestas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $carrera;

    /**
     * @ORM\ManyToOne(targetEntity=ApuestaTipo::class, inversedBy="apuestas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $tipo;

    /**
     * @ORM\Column(type="integer")
     */
    private $monto;

    /**
     * @ORM\ManyToOne(targetEntity=Perfil::class, inversedBy="ganadores")
     */
    private $ganador;

    /**
     * @ORM\OneToMany(targetEntity=ApuestaDetalle::class, mappedBy="apuesta", orphanRemoval=true, cascade={"persist"})
     */
    private $apuestaDetalles;

    /**
     * @ORM\OneToOne(targetEntity=Cuenta::class, mappedBy="apuesta", cascade={"persist", "remove"})
     */
    private $cuenta;




    public function __construct()
    {
        $this->apuestaDetalles = new ArrayCollection();
        $this->cuentas = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCarrera(): ?Carrera
    {
        return $this->carrera;
    }

    public function setCarrera(?Carrera $carrera): self
    {
        $this->carrera = $carrera;

        return $this;
    }

    public function getTipo(): ?ApuestaTipo
    {
        return $this->tipo;
    }

    public function setTipo(?ApuestaTipo $tipo): self
    {
        $this->tipo = $tipo;

        return $this;
    }

    public function getMonto(): ?int
    {
        return $this->monto;
    }

    public function setMonto(int $monto): self
    {
        $this->monto = $monto;

        return $this;
    }


    public function getGanador(): ?Perfil
    {
        return $this->ganador;
    }

    public function setGanador(?Perfil $ganador): self
    {
        $this->ganador = $ganador;

        return $this;
    }



    /**
     * @return Collection|ApuestaDetalle[]
     */
    public function getApuestaDetalles(): Collection
    {
        return $this->apuestaDetalles;
    }

    public function addApuestaDetalle(ApuestaDetalle $apuestaDetalle): self
    {
        if (!$this->apuestaDetalles->contains($apuestaDetalle)) {
            $this->apuestaDetalles[] = $apuestaDetalle;
            $apuestaDetalle->setApuesta($this);
        }

        return $this;
    }

    public function removeApuestaDetalle(ApuestaDetalle $apuestaDetalle): self
    {
        if ($this->apuestaDetalles->removeElement($apuestaDetalle)) {
            // set the owning side to null (unless already changed)
            if ($apuestaDetalle->getApuesta() === $this) {
                $apuestaDetalle->setApuesta(null);
            }
        }

        return $this;
    }

    public function getCuenta(): ?Cuenta
    {
        return $this->cuenta;
    }

    public function setCuenta(Cuenta $cuenta): self
    {
        // set the owning side of the relation if necessary
        if ($cuenta->getApuesta() !== $this) {
            $cuenta->setApuesta($this);
        }

        $this->cuenta = $cuenta;

        return $this;
    }




}
