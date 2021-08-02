<?php

namespace App\Entity;

use App\Repository\CorreccionRepository;
use Doctrine\ORM\Mapping as ORM;

//behaviors
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=CorreccionRepository::class)
 */
class Correccion
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
     * @ORM\ManyToOne(targetEntity=Apuesta::class, inversedBy="correccions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $apuesta;

    /**
     * @ORM\Column(type="text")
     */
    private $observacion;

    /**
     * @ORM\Column(type="text")
     */
    private $observacion_sistema;

    public function getId(): ?int
    {
        return $this->id;
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


    public function getObservacion(): ?string
    {
        return $this->observacion;
    }

    public function setObservacion(string $observacion): self
    {
        $this->observacion = $observacion;

        return $this;
    }

    public function getObservacionSistema(): ?string
    {
        return $this->observacion_sistema;
    }

    public function setObservacionSistema(string $observacion_sistema): self
    {
        $this->observacion_sistema = $observacion_sistema;

        return $this;
    }
}
