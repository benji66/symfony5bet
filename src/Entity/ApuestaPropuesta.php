<?php

namespace App\Entity;

use App\Repository\ApuestaPropuestaRepository;
use Doctrine\ORM\Mapping as ORM;


//behaviors
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=ApuestaPropuestaRepository::class)
 */
class ApuestaPropuesta
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
     * @ORM\ManyToOne(targetEntity=Carrera::class, inversedBy="apuestaPropuestas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $carrera;

    /**
     * @ORM\ManyToOne(targetEntity=ApuestaTipo::class, inversedBy="apuestaPropuestas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $tipo;

    /**
     * @ORM\ManyToOne(targetEntity=Perfil::class, inversedBy="apuestaPropuestas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $jugador;

    /**
     * @ORM\Column(type="integer")
     */
    private $monto;

    /**
     * @ORM\Column(type="json")
     */
    private $caballos = [];

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

    public function getJugador(): ?Perfil
    {
        return $this->jugador;
    }

    public function setJugador(?Perfil $jugador): self
    {
        $this->jugador = $jugador;

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

    public function getCaballos(): ?array
    {
        return $this->caballos;
    }

    public function setCaballos(array $caballos): self
    {
        $this->caballos = $caballos;

        return $this;
    }
}
