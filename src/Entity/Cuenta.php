<?php

namespace App\Entity;

use App\Repository\CuentaRepository;
use Doctrine\ORM\Mapping as ORM;

//behaviors
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;


/**
 * @ORM\Entity(repositoryClass=CuentaRepository::class)
 */
class Cuenta
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
     * @ORM\ManyToOne(targetEntity=Gerencia::class, inversedBy="cuentas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $gerencia;

    /**
     * @ORM\Column(type="float")
     */
    private $saldo_casa;

    /**
     * @ORM\Column(type="float")
     */
    private $saldo_sistema;


    /**
     * @ORM\Column(type="float")
     */
    private $saldo_ganador;

    /**
     * @ORM\OneToOne(targetEntity=Apuesta::class, inversedBy="cuenta", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $apuesta;




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

    public function getSaldoCasa(): ?float
    {
        return $this->saldo_casa;
    }

    public function setSaldoCasa(float $saldo_casa): self
    {
        $this->saldo_casa = $saldo_casa;

        return $this;
    }

    public function getSaldoSistema(): ?float
    {
        return $this->saldo_sistema;
    }

    public function setSaldoSistema(float $saldo_sistema): self
    {
        $this->saldo_sistema = $saldo_sistema;

        return $this;
    }


    public function getSaldoGanador(): ?float
    {
        return $this->saldo_ganador;
    }

    public function setSaldoGanador(float $saldo_ganador): self
    {
        $this->saldo_ganador = $saldo_ganador;

        return $this;
    }

    public function getApuesta(): ?Apuesta
    {
        return $this->apuesta;
    }

    public function setApuesta(Apuesta $apuesta): self
    {
        $this->apuesta = $apuesta;

        return $this;

    } 

}
