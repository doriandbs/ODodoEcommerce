<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?int $priceHT = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imagePath = null;    

    #[ORM\ManyToOne(inversedBy: 'products')]
    private ?Category $category = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    private ?Taxes $taxes = null;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: CommandLine::class)]
    private Collection $commandLines;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: ProductCart::class)]
    private Collection $productCarts;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $deletedDate= null;
    
    #[ORM\OneToOne(targetEntity: File::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?File $image = null;

    #[ORM\Column]
    private ?string $img = null;

    #[ORM\Column]
    private ?bool $public;
    
    public function getPublic(): ?bool
    {
        return $this->public;
    }

    public function setPublic(bool $public): self
    {
        $this->public = $public;

        return $this;
    }


    public function __construct()
    {
        $this->commandLines = new ArrayCollection();
        $this->productCarts = new ArrayCollection();
    }

    public function getImagePath(): ?string{
        return $this->imagePath;
    }

    public function setImagePath(String $imagePath){
         $this->imagePath = $imagePath;
         return $this;
    }
    public function getDeletedDate(): DateTimeImmutable{
        return $this->deletedDate;
    }

    public function getImg(): ?string 
    {
        return $this->img;
    }

    public function setImg(?string $img): static
    {
        $this->img = $img;

        return $this;
    }

    public function getImage(): ?File 
    {
        return $this->image;
    }

    public function setImage(?File $image): static
    {
        $this->image = $image;

        return $this;
    }
    
    public function setDeletedDate(DateTimeImmutable $deletedDate): static
    {
        $this->deletedDate = $deletedDate;

        return $this;
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

    public function getPriceHT(): ?int
    {
        return $this->priceHT;
    }

    public function setPriceHT(int $priceHT): static
    {
        $this->priceHT = $priceHT;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getTaxes(): ?Taxes
    {
        return $this->taxes;
    }

    public function setTaxes(?Taxes $taxes): static
    {
        $this->taxes = $taxes;

        return $this;
    }

    /**
     * @return Collection<int, CommandLine>
     */
    public function getCommandLines(): Collection
    {
        return $this->commandLines;
    }

    public function addCommandLine(CommandLine $commandLine): static
    {
        if (!$this->commandLines->contains($commandLine)) {
            $this->commandLines->add($commandLine);
            $commandLine->setProduct($this);
        }

        return $this;
    }

    public function removeCommandLine(CommandLine $commandLine): static
    {
        if ($this->commandLines->removeElement($commandLine)) {
            // set the owning side to null (unless already changed)
            if ($commandLine->getProduct() === $this) {
                $commandLine->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ProductCart>
     */
    public function getProductCarts(): Collection
    {
        return $this->productCarts;
    }

    public function addProductCart(ProductCart $productCart): static
    {
        if (!$this->productCarts->contains($productCart)) {
            $this->productCarts->add($productCart);
            $productCart->setProduct($this);
        }

        return $this;
    }

    public function removeProductCart(ProductCart $productCart): static
    {
        if ($this->productCarts->removeElement($productCart)) {
            // set the owning side to null (unless already changed)
            if ($productCart->getProduct() === $this) {
                $productCart->setProduct(null);
            }
        }

        return $this;
    }

    public function getSlug(): string
    {
        $slugger = new AsciiSlugger();
        $slug = $slugger-> slug($this->name);
        return strtolower($slug);
    }
}
