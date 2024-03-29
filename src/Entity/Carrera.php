<?php

namespace App\Entity;

use App\Repository\CarreraRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;


//behaviors
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
/**
 * @ORM\Entity(repositoryClass=CarreraRepository::class)
 * @UniqueEntity(
 *     fields={"numero_carrera", "hipodromo", "fecha"},
 *     errorPath="fecha", 
 *     message="Esta carrera ya ha sido cargada previamente"
 * )
 */
class Carrera
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
     * @ORM\Column(type="date")
     */
    private $fecha;


    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status;

    /**
     * @ORM\Column(type="integer")
     */
    private $numero_carrera;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $orden_oficial = [];

    /**
     * @ORM\ManyToOne(targetEntity=Local::class, inversedBy="carreras")
     * @ORM\JoinColumn(nullable=false)
     */
    private $hipodromo;

    /**
     * @ORM\ManyToOne(targetEntity=Gerencia::class, inversedBy="carreras")
     * @ORM\JoinColumn(nullable=false)
     */
    private $gerencia;

    /**
     * @ORM\OneToMany(targetEntity=Apuesta::class, mappedBy="carrera", cascade={"persist"})
     */
    private $apuestas;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $corre_lista;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $pagado_by;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $total_pagado;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $total_ganancia;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cerrado_by;

    /**
     * @ORM\OneToMany(targetEntity=ApuestaPropuesta::class, mappedBy="carrera")
     */
    private $apuestaPropuestas;

    /**
     * @ORM\OneToMany(targetEntity=Banca::class, mappedBy="carrera")
     */
    private $bancas;

   

    public function __construct()
    {
        $this->apuestas = new ArrayCollection();
        $this->apuestaPropuestas = new ArrayCollection();
        $this->bancas = new ArrayCollection();
       
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFecha(): ?\DateTimeInterface
    {
        return $this->fecha;
    }

    public function setFecha(\DateTimeInterface $fecha): self
    {
        $this->fecha = $fecha;

        return $this;
    }



    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getNumeroCarrera(): ?int
    {
        return $this->numero_carrera;
    }

    public function setNumeroCarrera(int $numero_carrera): self
    {
        $this->numero_carrera = $numero_carrera;

        return $this;
    }

    public function getOrdenOficial(): ?array
    {
        return $this->orden_oficial;
    }

    public function setOrdenOficial(?array $orden_oficial): self
    {
        $this->orden_oficial = $orden_oficial;

        return $this;
    }

    public function getHipodromo(): ?Local
    {
        return $this->hipodromo;
    }

    public function setHipodromo(?Local $hipodromo): self
    {
        $this->hipodromo = $hipodromo;

        return $this;
    }

    public function getGerencia(): ?Gerencia
    {
        return $this->gerencia;
    }

    public function setGerencia(?Gerencia $gerencia): self
    {
        $this->gerencia = $gerencia;

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
            $apuesta->setCarrera($this);
        }

        return $this;
    }

    public function removeApuesta(Apuesta $apuesta): self
    {
        if ($this->apuestas->removeElement($apuesta)) {
            // set the owning side to null (unless already changed)
            if ($apuesta->getCarrera() === $this) {
                $apuesta->setCarrera(null);
            }
        }

        return $this;
    }


    public function getCorreLista(): ?bool
    {
        return $this->corre_lista;
    }

    public function setCorreLista(bool $corre_lista): self
    {
        $this->corre_lista = $corre_lista;
        return $this;
    }


    public function getPagadoBy(): ?string
    {
        return $this->pagado_by;
    }

    public function setPagadoBy(?string $pagado_by): self
    {
        $this->pagado_by = $pagado_by;
        return $this;
    }

    public function getTotalPagado(): ?float
    {
        //return round($this->total_pagado,2,PHP_ROUND_HALF_DOWN);
        return $this->total_pagado;
    }

    public function setTotalPagado(?float $total_pagado): self
    {
        $this->total_pagado = round($total_pagado,2,PHP_ROUND_HALF_DOWN);

        return $this;
    }

    public function getTotalGanancia(): ?float
    {
        //return round($this->total_ganancia,2,PHP_ROUND_HALF_DOWN);
        return $this->total_ganancia;
    }

    public function setTotalGanancia(?float $total_ganancia): self
    {
        $this->total_ganancia = round($total_ganancia,2,PHP_ROUND_HALF_DOWN);

        return $this;
    }

    public function getCerradoBy(): ?string
    {
        return $this->cerrado_by;
    }

    public function setCerradoBy(?string $cerrado_by): self
    {
        $this->cerrado_by = $cerrado_by;

        return $this;
    }

    /**
     * @return Collection|Propuesta[]
     */
    public function getPropuestas(): Collection
    {
        return $this->propuestas;
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
            $apuestaPropuesta->setCarrera($this);
        }

        return $this;
    }

    public function removeApuestaPropuesta(ApuestaPropuesta $apuestaPropuesta): self
    {
        if ($this->apuestaPropuestas->removeElement($apuestaPropuesta)) {
            // set the owning side to null (unless already changed)
            if ($apuestaPropuesta->getCarrera() === $this) {
                $apuestaPropuesta->setCarrera(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Banca[]
     */
    public function getBancas(): Collection
    {
        return $this->bancas;
    }

    public function addBanca(Banca $banca): self
    {
        if (!$this->bancas->contains($banca)) {
            $this->bancas[] = $banca;
            $banca->setCarrera($this);
        }

        return $this;
    }

    public function removeBanca(Banca $banca): self
    {
        if ($this->bancas->removeElement($banca)) {
            // set the owning side to null (unless already changed)
            if ($banca->getCarrera() === $this) {
                $banca->setCarrera(null);
            }
        }

        return $this;
    }

 
}
