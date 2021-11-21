<?php

namespace App\Entity;

use App\Repository\TraspasoRepository;
use Doctrine\ORM\Mapping as ORM;

//behaviors
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=TraspasoRepository::class)
 */
class Traspaso
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
     * @ORM\ManyToOne(targetEntity=Gerencia::class, inversedBy="traspasos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $gerencia;


    /**
     * @ORM\ManyToOne(targetEntity=Perfil::class, inversedBy="traspaso_descuentos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $descuento;

    /**
     * @ORM\ManyToOne(targetEntity=Perfil::class, inversedBy="traspaso_abonos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $abono;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $observacion;

    /**
     * @ORM\Column(type="float")
     */
    private $monto;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDescuento(): ?Perfil
    {
        return $this->descuento;
    }

    public function setDescuento(?Perfil $descuento): self
    {
        $this->descuento = $descuento;

        return $this;
    }

    public function getAbono(): ?Perfil
    {
        return $this->abono;
    }

    public function setAbono(?Perfil $abono): self
    {
        $this->abono = $abono;

        return $this;
    }

    public function getObservacion(): ?string
    {
        return $this->observacion;
    }

    public function setObservacion(?string $observacion): self
    {
        $this->observacion = $observacion;

        return $this;
    }

    public function getMonto(): ?float
    {
        return $this->monto;
    }

    public function setMonto(float $monto): self
    {
        $this->monto = $monto;

        return $this;
    }
}
