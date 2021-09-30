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
     * @ORM\Column(type="json")
     */
    private $roles = [];
    
    /**
     * @ORM\Column(type="string", length=25, nullable=false, unique=false)
     * @Groups({"read"})
     */
    private $nickname;

    /**
     * @ORM\Column(type="integer", nullable=true)
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





     

    public function __construct()
    {
        $this->adjuntoPagos = new ArrayCollection();
          $this->ganadores = new ArrayCollection();
        $this->apuestaDetalles = new ArrayCollection();
        $this->apuestaPropuestas = new ArrayCollection();
        $this->pagoPersonals = new ArrayCollection();
  
        $this->retiroSaldos = new ArrayCollection();
        $this->perfilBancos = new ArrayCollection();
    
       

     
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

    public function getSaldo(): ?int
    {
        return $this->saldo;
    }

    public function setSaldo(?int $saldo): self
    {
        $this->saldo = $saldo;

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


 

 
}
