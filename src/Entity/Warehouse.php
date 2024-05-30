<?php

namespace App\Entity;

use App\Repository\WarehouseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WarehouseRepository::class)]
class Warehouse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?bool $isAvailable = null;

    /**
     * @var Collection<int, WarehouseProduct>
     */
    #[ORM\OneToMany(targetEntity: WarehouseProduct::class, mappedBy: 'warehouse', orphanRemoval: true)]
    private Collection $warehouseProducts;

    public function __construct()
    {
        $this->warehouseProducts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getIsAvailable(): ?bool
    {
        return $this->isAvailable;
    }

    public function setIsAvailable(bool $isAvailable): static
    {
        $this->isAvailable = $isAvailable;

        return $this;
    }

    /**
     * @return Collection<int, WarehouseProduct>
     */
    public function getWarehouseProducts(): Collection
    {
        return $this->warehouseProducts;
    }

    public function addWarehouseProduct(WarehouseProduct $warehouseProduct): static
    {
        if (!$this->warehouseProducts->contains($warehouseProduct)) {
            $this->warehouseProducts->add($warehouseProduct);
            $warehouseProduct->setWarehouse($this);
        }

        return $this;
    }

    public function removeWarehouseProduct(WarehouseProduct $warehouseProduct): static
    {
        if ($this->warehouseProducts->removeElement($warehouseProduct)) {
            // set the owning side to null (unless already changed)
            if ($warehouseProduct->getWarehouse() === $this) {
                $warehouseProduct->setWarehouse(null);
            }
        }

        return $this;
    }
}
