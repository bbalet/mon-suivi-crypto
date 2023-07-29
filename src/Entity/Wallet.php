<?php

namespace App\Entity;

use App\Repository\WalletRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WalletRepository::class)]
class Wallet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'wallet', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToMany(targetEntity: CryptoCurrency::class)]
    private Collection $cryptocurrencies;

    public function __construct()
    {
        $this->cryptocurrencies = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, CryptoCurrency>
     */
    public function getCryptocurrencies(): Collection
    {
        return $this->cryptocurrencies;
    }

    public function addCryptocurrency(CryptoCurrency $cryptocurrency): static
    {
        if (!$this->cryptocurrencies->contains($cryptocurrency)) {
            $this->cryptocurrencies->add($cryptocurrency);
        }

        return $this;
    }

    public function removeCryptocurrency(CryptoCurrency $cryptocurrency): static
    {
        $this->cryptocurrencies->removeElement($cryptocurrency);

        return $this;
    }

}
