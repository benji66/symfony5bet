<?php

namespace App\Entity;

use App\Repository\ApuestaDetalleRepository;
use Doctrine\ORM\Mapping as ORM;

//behaviors
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=ApuestaDetalleRepository::class)
 */
class ApuestaDetalle
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
     * @ORM\ManyToOne(targetEntity=Perfil::class, inversedBy="apuestaDetalles", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $perfil;

    /**
     * @ORM\ManyToOne(targetEntity=Apuesta::class, inversedBy="apuestaDetalles")
     * @ORM\JoinColumn(nullable=false)
     */
    private $apuesta;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $caballos = [];

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

    public function getApuesta(): ?Apuesta
    {
        return $this->apuesta;
    }

    public function setApuesta(?Apuesta $apuesta): self
    {
        $this->apuesta = $apuesta;

        return $this;
    }

    public function getCaballos(): ?array
    {
        return $this->caballos;
    }

    public function setCaballos(?array $caballos): self
    {
        $this->caballos = $caballos;

        return $this;
    }
}
