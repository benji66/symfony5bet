<?php

namespace App\Entity;

use App\Repository\CarreraRepository;
use Doctrine\ORM\Mapping as ORM;


//behaviors
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;


/**
 * @ORM\Entity(repositoryClass=CarreraRepository::class)
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
     * @ORM\Column(type="integer")
     */
    private $cantidad_caballos;

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

    public function getCantidadCaballos(): ?int
    {
        return $this->cantidad_caballos;
    }

    public function setCantidadCaballos(int $cantidad_caballos): self
    {
        $this->cantidad_caballos = $cantidad_caballos;

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
}
