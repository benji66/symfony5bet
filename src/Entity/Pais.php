<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\PaisRepository;
use Doctrine\ORM\Mapping as ORM;

//behaviors
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;


use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Gedmo\Loggable 
 * @ORM\Entity(repositoryClass=PaisRepository::class)
 */
class Pais
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
     * @Assert\Length(
     *      min = 3,
     *      max = 255,
     *      minMessage = "El valor debe contener minimo {{ limit }} caracteres",
     *      maxMessage = "El valor no debe superar los {{ limit }} caracteres"
     * )
     *  
     * @Gedmo\Versioned   
     * @ORM\Column(type="string", length=255)
     */
    private $nombre;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $poblacion;

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

    public function getPoblacion(): ?int
    {
        return $this->poblacion;
    }

    public function setPoblacion(int $poblacion): self
    {
        $this->poblacion = $poblacion;

        return $this;
    }
}
