<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\GerenciaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

//behaviors
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;


use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @Gedmo\Loggable 
 * @ORM\Entity(repositoryClass=GerenciaRepository::class)
 * @UniqueEntity(
 *     fields={"nombre"}, 
 *     message="Este nombre ya esta siendo usado por otra entidad."
 * )
 */

class Gerencia
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
     * @ORM\Column(type="string", length=255)
     */
    private $nombre;

    /**
     * @ORM\OneToMany(targetEntity=Perfil::class, mappedBy="gerencia", cascade={"persist", "remove"})
     */
    private $perfils;

    /**
     * @ORM\OneToMany(targetEntity=AdjuntoPago::class, mappedBy="gerencia")
     */
    private $adjuntoPagos;

    /**
     * @ORM\OneToMany(targetEntity=MetodoPago::class, mappedBy="gerencia", cascade={"persist"})
     */
    private $metodoPagos;

    /**
     * @ORM\OneToMany(targetEntity=Carrera::class, mappedBy="gerencia", cascade={"persist"})
     */
    private $carreras;

    /**
     * @ORM\OneToMany(targetEntity=Cuenta::class, mappedBy="gerencia", orphanRemoval=true)
     */
    private $cuentas;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $saldo_acumulado;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $reiniciado_by;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $reiniciado_at;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $imagen;

    /**
     * @ORM\OneToMany(targetEntity=Traspaso::class, mappedBy="gerencia")
     */
    private $traspasos;


    public function __construct()
    {
        $this->perfils = new ArrayCollection();
        $this->adjuntoPagos = new ArrayCollection();
        $this->metodoPagos = new ArrayCollection();
        $this->carreras = new ArrayCollection();
        $this->cuentas = new ArrayCollection();
        $this->traspasos = new ArrayCollection();
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
        $this->nombre = strtoupper($nombre);

        return $this;
    }

    /**
     * @return Collection|Perfil[]
     */
    public function getPerfils(): Collection
    {
        return $this->perfils;
    }

    public function addPerfil(Perfil $perfil): self
    {
        if (!$this->perfils->contains($perfil)) {
            $this->perfils[] = $perfil;
            $perfil->setGerencia($this);
        }

        return $this;
    }

    public function removePerfil(Perfil $perfil): self
    {
        if ($this->perfils->removeElement($perfil)) {
            // set the owning side to null (unless already changed)
            if ($perfil->getGerencia() === $this) {
                $perfil->setGerencia(null);
            }
        }

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
            $adjuntoPago->setGerencia($this);
        }

        return $this;
    }

    public function removeAdjuntoPago(AdjuntoPago $adjuntoPago): self
    {
        if ($this->adjuntoPagos->removeElement($adjuntoPago)) {
            // set the owning side to null (unless already changed)
            if ($adjuntoPago->getGerencia() === $this) {
                $adjuntoPago->setGerencia(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|MetodoPago[]
     */
    public function getMetodoPagos(): Collection
    {
        return $this->metodoPagos;
    }

    public function addMetodoPago(MetodoPago $metodoPago): self
    {
        if (!$this->metodoPagos->contains($metodoPago)) {
            $this->metodoPagos[] = $metodoPago;
            $metodoPago->setGerencia($this);
        }

        return $this;
    }

    public function removeMetodoPago(MetodoPago $metodoPago): self
    {
        if ($this->metodoPagos->removeElement($metodoPago)) {
            // set the owning side to null (unless already changed)
            if ($metodoPago->getGerencia() === $this) {
                $metodoPago->setGerencia(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Carrera[]
     */
    public function getCarreras(): Collection
    {
        return $this->carreras;
    }

    public function addCarrera(Carrera $carrera): self
    {
        if (!$this->carreras->contains($carrera)) {
            $this->carreras[] = $carrera;
            $carrera->setGerencia($this);
        }

        return $this;
    }

    public function removeCarrera(Carrera $carrera): self
    {
        if ($this->carreras->removeElement($carrera)) {
            // set the owning side to null (unless already changed)
            if ($carrera->getGerencia() === $this) {
                $carrera->setGerencia(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Cuenta[]
     */
    public function getCuentas(): Collection
    {
        return $this->cuentas;
    }

    public function addCuenta(Cuenta $cuenta): self
    {
        if (!$this->cuentas->contains($cuenta)) {
            $this->cuentas[] = $cuenta;
            $cuenta->setGerencia($this);
        }

        return $this;
    }

    public function removeCuenta(Cuenta $cuenta): self
    {
        if ($this->cuentas->removeElement($cuenta)) {
            // set the owning side to null (unless already changed)
            if ($cuenta->getGerencia() === $this) {
                $cuenta->setGerencia(null);
            }
        }

        return $this;
    }

    public function getSaldoAcumulado(): ?float
    {
        return round($this->saldo_acumulado,2,PHP_ROUND_HALF_DOWN);
    }

    public function setSaldoAcumulado(?float $saldo_acumulado): self
    {
        $this->saldo_acumulado = round($saldo_acumulado,2,PHP_ROUND_HALF_DOWN);

        return $this;
    }

    public function getReiniciadoBy(): ?string
    {
        return $this->reiniciado_by;
    }

    public function setReiniciadoBy(?string $reiniciado_by): self
    {
        $this->reiniciado_by = $reiniciado_by;

        return $this;
    }

    public function getReiniciadoAt(): ?\DateTimeInterface
    {
        return $this->reiniciado_at;
    }

    public function setReiniciadoAt(?\DateTimeInterface $reiniciado_at): self
    {
        $this->reiniciado_at = $reiniciado_at;

        return $this;
    }

    public function getImagen(): ?string
    {
        return $this->imagen;
    }

    public function setImagen(?string $imagen): self
    {
        $this->imagen = $imagen;

        return $this;
    }

    /**
     * @return Collection|Traspaso[]
     */
    public function getTraspasos(): Collection
    {
        return $this->traspasos;
    }

    public function addTraspaso(Traspaso $traspaso): self
    {
        if (!$this->traspasos->contains($traspaso)) {
            $this->traspasos[] = $traspaso;
            $traspaso->setGerencia($this);
        }

        return $this;
    }

    public function removeTraspaso(Traspaso $traspaso): self
    {
        if ($this->traspasos->removeElement($traspaso)) {
            // set the owning side to null (unless already changed)
            if ($traspaso->getGerencia() === $this) {
                $traspaso->setGerencia(null);
            }
        }

        return $this;
    }


}
