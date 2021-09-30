<?php

namespace App\Entity;

use App\Repository\PagoPersonalRepository;
use Doctrine\ORM\Mapping as ORM;



//behaviors
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=PagoPersonalRepository::class)
 */
class PagoPersonal
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
     * @ORM\ManyToOne(targetEntity=Perfil::class, inversedBy="pagoPersonals")
     * @ORM\JoinColumn(nullable=false)
     */
    private $perfil;

    /**
     * @ORM\Column(type="integer")
     */
    private $monto;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $concepto;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $observacion;

    /**
     * @ORM\ManyToOne(targetEntity=MetodoPago::class, inversedBy="adjuntoPagos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $metodo_pago;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $numero_referencia;  

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $ruta;      

    public function getId(): ?int
    {
        return $this->id;
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

    public function getMonto(): ?int
    {
        return $this->monto;
    }

    public function setMonto(int $monto): self
    {
        $this->monto = $monto;

        return $this;
    }

    public function getConcepto(): ?string
    {
        return $this->concepto;
    }

    public function setConcepto(string $concepto): self
    {
        $this->concepto = $concepto;

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
    public function getMetodoPago(): ?MetodoPago
    {
        return $this->metodo_pago;
    }

    public function setMetodoPago(?MetodoPago $metodo_pago): self
    {
        $this->metodo_pago = $metodo_pago;

        return $this;
    }


    public function getNumeroReferencia(): ?string
    {
        return $this->numero_referencia;
    }

    public function setNumeroReferencia(string $numero_referencia): self
    {
        $this->numero_referencia = $numero_referencia;

        return $this;
    }    

    public function getRuta(): ?string
    {
        return $this->ruta;
    }

    public function setRuta(string $ruta): self
    {
        $this->ruta = $ruta;

        return $this;
    }    
}
