<?php

namespace App\Entity;

use App\Repository\BancaRepository;
use Doctrine\ORM\Mapping as ORM;


//behaviors
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=BancaRepository::class)
 */
class Banca
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
     * @ORM\ManyToOne(targetEntity=Perfil::class, inversedBy="usuario_bancas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $usuario;

    /**
     * @ORM\ManyToOne(targetEntity=Perfil::class, inversedBy="cliente_bancas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $cliente;

    /**
     * @ORM\ManyToOne(targetEntity=Carrera::class, inversedBy="bancas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $carrera;

    /**
     * @ORM\Column(type="boolean")
     */
    private $juega;

    /**
     * @ORM\Column(type="float")
     */
    private $monto;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $procesado_at;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsuario(): ?Perfil
    {
        return $this->usuario;
    }

    public function setUsuario(?Perfil $usuario): self
    {
        $this->usuario = $usuario;

        return $this;
    }

    public function getCliente(): ?Perfil
    {
        return $this->cliente;
    }

    public function setCliente(?Perfil $cliente): self
    {
        $this->cliente = $cliente;

        return $this;
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


    public function getJuega(): ?bool
    {
        return $this->juega;
    }

    public function setJuega(bool $juega): self
    {
        $this->juega = $juega;

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

    public function getProcesadoAt(): ?\DateTimeInterface
    {
        return $this->procesado_at;
    }

    public function setProcesadoAt(?\DateTimeInterface $procesado_at): self
    {
        $this->procesado_at = $procesado_at;

        return $this;
    }
}
