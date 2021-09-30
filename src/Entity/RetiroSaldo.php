<?php

namespace App\Entity;
//
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

//behaviors
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RetiroSaldoRepository")
 */
class RetiroSaldo
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
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $ruta;

  

    /**
     * @ORM\Column(type="integer")
     */
    private $monto;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $validado_by;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    private $numero_referencia;

    /**
     * @ORM\ManyToOne(targetEntity=Gerencia::class, inversedBy="adjuntoPagos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $gerencia;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $observacion;

    /**
     * @ORM\ManyToOne(targetEntity=MetodoPago::class, inversedBy="retiroSaldos")
     * @ORM\JoinColumn(nullable=true)
     */
    private $metodo_pago;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $validado;



    /**
     * @ORM\ManyToOne(targetEntity=Perfil::class, inversedBy="retiroSaldos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $perfil;

    /**
     * @ORM\ManyToOne(targetEntity=PerfilBanco::class, inversedBy="retiro_saldos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $perfilBanco;







    public function getId(): ?int
    {
        return $this->id;
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

    public function getMonto(): ?int
    {
        return $this->monto;
    }

    public function setMonto(int $monto): self
    {
        $this->monto = $monto;

        return $this;
    }

    public function getValidadoBy(): ?string
    {
        return $this->validado_by;
    }

    public function setValidadoBy(?string $validado_by): self
    {
        $this->validado_by = $validado_by;

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

    public function getGerencia(): ?Gerencia
    {
        return $this->gerencia;
    }

    public function setGerencia(?Gerencia $gerencia): self
    {
        $this->gerencia = $gerencia;

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

    public function getValidado(): ?bool
    {
        return $this->validado;
    }

    public function setValidado(?bool $validado): self
    {
        $this->validado = $validado;

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

    public function getPerfilBanco(): ?PerfilBanco
    {
        return $this->perfilBanco;
    }

    public function setPerfilBanco(?PerfilBanco $perfilBanco): self
    {
        $this->perfilBanco = $perfilBanco;

        return $this;
    }





}
