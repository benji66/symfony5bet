<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;

use App\Entity\Dependencia;

use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

//behaviors
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;


/**
 * @ApiResource(normalizationContext={"groups"={"read"}})
 * @Gedmo\Loggable 
 * @ORM\Entity(repositoryClass="App\Repository\PerfilRepository")
 * @UniqueEntity(
 *     fields={"gerencia", "nickname"},
 *     errorPath="nickname", 
 *     message="Este nickname ya esta siendo usado por otra persona en esta gerencia."
 * )

 * @UniqueEntity(
 *     fields={"gerencia", "usuario"},
 *     errorPath="gerencia", 
 *     message="Esta gerencia ya esta siendo usada por el usuario."
 * )
 */
class Perfil
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
     * @Groups({"read"})
     */
    private $id;


    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="json")
     */
    private $roles = [];
    
    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="string", length=25, nullable=false, unique=false)
     * @Groups({"read"})
     */
    private $nickname;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"read"})
     */
    private $saldo;

    /**
     * @ORM\ManyToOne(targetEntity=Gerencia::class, inversedBy="perfils", cascade={"persist"})
     */
    private $gerencia;


    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="perfils", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $usuario;

    /**
     * @ORM\Column(type="boolean")
     */
    private $activo;

    /**
     * @ORM\OneToMany(targetEntity=AdjuntoPago::class, mappedBy="perfil", orphanRemoval=true)
     */
    private $adjuntoPagos;




    /**
     * @ORM\OneToMany(targetEntity=Apuesta::class, mappedBy="ganador")
     */
    private $ganadores;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $saldo_ilimitado;

    /**
     * @ORM\OneToMany(targetEntity=ApuestaDetalle::class, mappedBy="perfil", cascade={"persist"})
     */
    private $apuestaDetalles;

    /**
     * @ORM\OneToMany(targetEntity=ApuestaPropuesta::class, mappedBy="jugador")
     */
    private $apuestaPropuestas;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="integer", nullable=true)
     */
    private $sueldo;

    /**
     * @ORM\OneToMany(targetEntity=PagoPersonal::class, mappedBy="perfil")
     */
    private $pagoPersonals;




    /**
     * @ORM\OneToMany(targetEntity=RetiroSaldo::class, mappedBy="perfil")
     */
    private $retiroSaldos;

    /**
     * @ORM\OneToMany(targetEntity=PerfilBanco::class, mappedBy="perfil")
     */
    private $perfilBancos;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="float", nullable=true)
     */
    private $porcentaje_ganar;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="float", nullable=true)
     */
    private $porcentaje_perder;

    /**
     * @ORM\OneToMany(targetEntity=Banca::class, mappedBy="usuario")
     */
    private $usuario_bancas;

    /**
     * @ORM\OneToMany(targetEntity=Banca::class, mappedBy="cliente")
     */
    private $cliente_bancas;

    /**
     * @ORM\OneToMany(targetEntity=Cuenta::class, mappedBy="ganador")
     */
    private $cuentas_ganador;

    /**
     * @ORM\OneToMany(targetEntity=Cuenta::class, mappedBy="perdedor")
     */
    private $cuentas_perdedor;

    /**
     * @ORM\OneToMany(targetEntity=Traspaso::class, mappedBy="descuento")
     */
    private $traspaso_descuentos;

    /**
     * @ORM\OneToMany(targetEntity=Traspaso::class, mappedBy="abono")
     */
    private $traspaso_abonos;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="integer", nullable=true)
     */
    private $limite;  

     

    public function __construct()
    {
        $this->adjuntoPagos = new ArrayCollection();
          $this->ganadores = new ArrayCollection();
        $this->apuestaDetalles = new ArrayCollection();
        $this->apuestaPropuestas = new ArrayCollection();
        $this->pagoPersonals = new ArrayCollection();
  
        $this->retiroSaldos = new ArrayCollection();
        $this->perfilBancos = new ArrayCollection();
        $this->usuario_bancas = new ArrayCollection();
        $this->cliente_bancas = new ArrayCollection();
        $this->cuentas_ganador = new ArrayCollection();
        $this->cuentas_perdedor = new ArrayCollection();
        $this->traspaso_retiros = new ArrayCollection();
        $this->traspaso_descuentos = new ArrayCollection();
        $this->traspaso_abonos = new ArrayCollection();
    
       

     
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

    public function getApellido(): ?string
    {
        return $this->apellido;
    }

    public function setApellido(string $apellido): self
    {
        $this->apellido = $apellido;

        return $this;
    }

    public function getTelefono(): ?string
    {
        return $this->telefono;
    }

    public function setTelefono(?string $telefono): self
    {
        $this->telefono = $telefono;

        return $this;
    }


    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function setNickname(?string $nickname): self
    {
        $this->nickname = $nickname;

        return $this;
    }

    public function getSaldo(): ?float
    {
        //return  round($this->saldo,2,PHP_ROUND_HALF_DOWN);
        return $this->saldo;
    }

    public function setSaldo(?float $saldo): self
    {
        $this->saldo = round($saldo,2,PHP_ROUND_HALF_DOWN);

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

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

    public function getUsuario(): ?User
    {
        return $this->usuario;
    }

    public function setUsuario(?User $usuario): self
    {
        $this->usuario = $usuario;

        return $this;
    }

    public function getActivo(): ?bool
    {
        return $this->activo;
    }

    public function setActivo(bool $activo): self
    {
        $this->activo = $activo;

        return $this;
    }

    /**
     * @return Collection|AdjuntoPago[]
     */
    public function getAdjuntoPagos(): Collection
    {
        return $this->adjuntoPagos;
    }

    public function addAdjuntoPago(AdjuntoPago $adjuntoPago): self
    {
        if (!$this->adjuntoPagos->contains($adjuntoPago)) {
            $this->adjuntoPagos[] = $adjuntoPago;
            $adjuntoPago->setPerfil($this);
        }

        return $this;
    }

    public function removeAdjuntoPago(AdjuntoPago $adjuntoPago): self
    {
        if ($this->adjuntoPagos->removeElement($adjuntoPago)) {
            // set the owning side to null (unless already changed)
            if ($adjuntoPago->getPerfil() === $this) {
                $adjuntoPago->setPerfil(null);
            }
        }

        return $this;
    }



    /**
     * @return Collection|Apuesta[]
     */
    public function getGanadores(): Collection
    {
        return $this->ganadores;
    }

    public function getSaldoIlimitado(): ?bool
    {
        return $this->saldo_ilimitado;
    }

    public function setSaldoIlimitado(?bool $saldo_ilimitado): self
    {
        $this->saldo_ilimitado = $saldo_ilimitado;

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
            $apuestaDetalle->setPerfil($this);
        }

        return $this;
    }

    public function removeApuestaDetalle(ApuestaDetalle $apuestaDetalle): self
    {
        if ($this->apuestaDetalles->removeElement($apuestaDetalle)) {
            // set the owning side to null (unless already changed)
            if ($apuestaDetalle->getPerfil() === $this) {
                $apuestaDetalle->setPerfil(null);
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
            $apuestaPropuesta->setJugador($this);
        }

        return $this;
    }

    public function removeApuestaPropuesta(ApuestaPropuesta $apuestaPropuesta): self
    {
        if ($this->apuestaPropuestas->removeElement($apuestaPropuesta)) {
            // set the owning side to null (unless already changed)
            if ($apuestaPropuesta->getJugador() === $this) {
                $apuestaPropuesta->setJugador(null);
            }
        }

        return $this;
    }

    public function getSueldo(): ?int
    {
        return $this->sueldo;
    }

    public function setSueldo(?int $sueldo): self
    {
        $this->sueldo = $sueldo;

        return $this;
    }


    /**
     * @return Collection|PerfilBanco[]
     */
    public function getPerfilBancos(): ?Collection
    {
        return $this->perfilBancos;
    }

    public function addPerfilBanco(PerfilBanco $perfilBanco): self
    {
        if (!$this->perfilBancos->contains($perfilBanco)) {
            $this->perfilBancos[] = $perfilBanco;
            $perfilBanco->setPerfil($this);
        }

        return $this;
    }

    public function removePerfilBanco(PerfilBanco $perfilBanco): self
    {
        if ($this->perfilBancos->removeElement($perfilBanco)) {
            // set the owning side to null (unless already changed)
            if ($perfilBanco->getPerfil() === $this) {
                $perfilBanco->setPerfil(null);
            }
        }

        return $this;
    }


    /**
     * @return Collection|PagoPersonal[]
     */
    public function getPagoPersonals(): Collection
    {
        return $this->pagoPersonals;
    }

    /**
     * @return Collection|RetiroSaldo[]
     */
    public function getRetiroSaldos(): Collection
    {
        return $this->retiroSaldos;
    }

    public function addRetiroSaldo(RetiroSaldo $retiroSaldo): self
    {
        if (!$this->retiroSaldos->contains($retiroSaldo)) {
            $this->retiroSaldos[] = $retiroSaldo;
            $retiroSaldo->setPerfil($this);
        }

        return $this;
    }

    public function removeRetiroSaldo(RetiroSaldo $retiroSaldo): self
    {
        if ($this->retiroSaldos->removeElement($retiroSaldo)) {
            // set the owning side to null (unless already changed)
            if ($retiroSaldo->getPerfil() === $this) {
                $retiroSaldo->setPerfil(null);
            }
        }

        return $this;
    }

    public function getPorcentajeGanar(): ?float
    {
        return $this->porcentaje_ganar;
    }

    public function setPorcentajeGanar(?float $porcentaje_ganar): self
    {
        $this->porcentaje_ganar = $porcentaje_ganar;

        return $this;
    }

    public function getPorcentajePerder(): ?float
    {
        return $this->porcentaje_perder;
    }

    public function setPorcentajePerder(?float $porcentaje_perder): self
    {
        $this->porcentaje_perder = $porcentaje_perder;

        return $this;
    }

    /**
     * @return Collection|Banca[]
     */
    public function getUsuarioBancas(): Collection
    {
        return $this->usuario_bancas;
    }

    public function addUsuarioBanca(Banca $usuarioBanca): self
    {
        if (!$this->usuario_bancas->contains($usuarioBanca)) {
            $this->usuario_bancas[] = $usuarioBanca;
            $usuarioBanca->setUsuario($this);
        }

        return $this;
    }

    public function removeUsuarioBanca(Banca $usuarioBanca): self
    {
        if ($this->usuario_bancas->removeElement($usuarioBanca)) {
            // set the owning side to null (unless already changed)
            if ($usuarioBanca->getUsuario() === $this) {
                $usuarioBanca->setUsuario(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Banca[]
     */
    public function getClienteBancas(): Collection
    {
        return $this->cliente_bancas;
    }

    public function addClienteBanca(Banca $clienteBanca): self
    {
        if (!$this->cliente_bancas->contains($clienteBanca)) {
            $this->cliente_bancas[] = $clienteBanca;
            $clienteBanca->setCliente($this);
        }

        return $this;
    }

    public function removeClienteBanca(Banca $clienteBanca): self
    {
        if ($this->cliente_bancas->removeElement($clienteBanca)) {
            // set the owning side to null (unless already changed)
            if ($clienteBanca->getCliente() === $this) {
                $clienteBanca->setCliente(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Cuenta[]
     */
    public function getCuentasGanador(): Collection
    {
        return $this->cuentas_ganador;
    }

    public function addCuentasGanador(Cuenta $cuentasGanador): self
    {
        if (!$this->cuentas_ganador->contains($cuentasGanador)) {
            $this->cuentas_ganador[] = $cuentasGanador;
            $cuentasGanador->setGanador($this);
        }

        return $this;
    }

    public function removeCuentasGanador(Cuenta $cuentasGanador): self
    {
        if ($this->cuentas_ganador->removeElement($cuentasGanador)) {
            // set the owning side to null (unless already changed)
            if ($cuentasGanador->getGanador() === $this) {
                $cuentasGanador->setGanador(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Cuenta[]
     */
    public function getCuentasPerdedor(): Collection
    {
        return $this->cuentas_perdedor;
    }

    public function addCuentasPerdedor(Cuenta $cuentasPerdedor): self
    {
        if (!$this->cuentas_perdedor->contains($cuentasPerdedor)) {
            $this->cuentas_perdedor[] = $cuentasPerdedor;
            $cuentasPerdedor->setPerdedor($this);
        }

        return $this;
    }

    public function removeCuentasPerdedor(Cuenta $cuentasPerdedor): self
    {
        if ($this->cuentas_perdedor->removeElement($cuentasPerdedor)) {
            // set the owning side to null (unless already changed)
            if ($cuentasPerdedor->getPerdedor() === $this) {
                $cuentasPerdedor->setPerdedor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Traspaso[]
     */
    public function getTraspasoDescuentos(): Collection
    {
        return $this->traspaso_descuentos;
    }

    public function addTraspasoDescuento(Traspaso $traspasoDescuento): self
    {
        if (!$this->traspaso_descuentos->contains($traspasoDescuento)) {
            $this->traspaso_descuentos[] = $traspasoDescuento;
            $traspasoDescuento->setDescuento($this);
        }

        return $this;
    }

    public function removeTraspasoDescuento(Traspaso $traspasoDescuento): self
    {
        if ($this->traspaso_descuentos->removeElement($traspasoDescuento)) {
            // set the owning side to null (unless already changed)
            if ($traspasoDescuento->getDescuento() === $this) {
                $traspasoDescuento->setDescuento(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Traspaso[]
     */
    public function getTraspasoAbonos(): Collection
    {
        return $this->traspaso_abonos;
    }

    public function addTraspasoAbono(Traspaso $traspasoAbono): self
    {
        if (!$this->traspaso_abonos->contains($traspasoAbono)) {
            $this->traspaso_abonos[] = $traspasoAbono;
            $traspasoAbono->setAbono($this);
        }

        return $this;
    }

    public function removeTraspasoAbono(Traspaso $traspasoAbono): self
    {
        if ($this->traspaso_abonos->removeElement($traspasoAbono)) {
            // set the owning side to null (unless already changed)
            if ($traspasoAbono->getAbono() === $this) {
                $traspasoAbono->setAbono(null);
            }
        }

        return $this;
    }


    public function getLimite(): ?int
    {
        return $this->limite;
    }

    public function setLimite(?int $limite): self
    {
        $this->limite = $limite;

        return $this;
    } 

 
}
