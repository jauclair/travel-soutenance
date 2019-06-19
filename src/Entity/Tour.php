<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TourRepository")
 */
class Tour
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="date")
     */
    private $departure_date;

    /**
     * @ORM\Column(type="date")
     */
    private $arrival_date;

    /**
     * @ORM\Column(type="boolean")
     */
    private $traveler_group;

    /**
     * @ORM\Column(type="integer")
     */
    private $price;

    /**
     * @ORM\Column(type="integer")
     */
    private $transfer_price;

    /**
     * @ORM\Column(type="integer")
     */
    private $cancel_price;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $option_1_desc;

    /**
     * @ORM\Column(type="integer")
     */
    private $option_1_price;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $option_2_desc;

    /**
     * @ORM\Column(type="integer")
     */
    private $option_2_price;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $option_3_desc;

    /**
     * @ORM\Column(type="integer")
     */
    private $option_3_price;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Order", mappedBy="travel", orphanRemoval=true)
     */
    private $orders;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Country", inversedBy="tours")
     * @ORM\JoinColumn(nullable=false)
     */
    private $country;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Flight", inversedBy="tours")
     */
    private $flights;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Accommodation", inversedBy="tours")
     */
    private $Accomodations;

    public function __construct()
    {
        $this->orders = new ArrayCollection();
        $this->flights = new ArrayCollection();
        $this->Accomodations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDepartureDate(): ?\DateTimeInterface
    {
        return $this->departure_date;
    }

    public function setDepartureDate(\DateTimeInterface $departure_date): self
    {
        $this->departure_date = $departure_date;

        return $this;
    }

    public function getArrivalDate(): ?\DateTimeInterface
    {
        return $this->arrival_date;
    }

    public function setArrivalDate(\DateTimeInterface $arrival_date): self
    {
        $this->arrival_date = $arrival_date;

        return $this;
    }

    public function getTravelerGroup(): ?bool
    {
        return $this->traveler_group;
    }

    public function setTravelerGroup(bool $traveler_group): self
    {
        $this->traveler_group = $traveler_group;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getTransferPrice(): ?int
    {
        return $this->transfer_price;
    }

    public function setTransferPrice(int $transfer_price): self
    {
        $this->transfer_price = $transfer_price;

        return $this;
    }

    public function getCancelPrice(): ?int
    {
        return $this->cancel_price;
    }

    public function setCancelPrice(int $cancel_price): self
    {
        $this->cancel_price = $cancel_price;

        return $this;
    }

    public function getOption1Desc(): ?string
    {
        return $this->option_1_desc;
    }

    public function setOption1Desc(?string $option_1_desc): self
    {
        $this->option_1_desc = $option_1_desc;

        return $this;
    }

    public function getOption1Price(): ?int
    {
        return $this->option_1_price;
    }

    public function setOption1Price(int $option_1_price): self
    {
        $this->option_1_price = $option_1_price;

        return $this;
    }

    public function getOption2Desc(): ?string
    {
        return $this->option_2_desc;
    }

    public function setOption2Desc(?string $option_2_desc): self
    {
        $this->option_2_desc = $option_2_desc;

        return $this;
    }

    public function getOption2Price(): ?int
    {
        return $this->option_2_price;
    }

    public function setOption2Price(int $option_2_price): self
    {
        $this->option_2_price = $option_2_price;

        return $this;
    }

    public function getOption3Desc(): ?string
    {
        return $this->option_3_desc;
    }

    public function setOption3Desc(?string $option_3_desc): self
    {
        $this->option_3_desc = $option_3_desc;

        return $this;
    }

    public function getOption3Price(): ?int
    {
        return $this->option_3_price;
    }

    public function setOption3Price(int $option_3_price): self
    {
        $this->option_3_price = $option_3_price;

        return $this;
    }

    /**
     * @return Collection|Order[]
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): self
    {
        if (!$this->orders->contains($order)) {
            $this->orders[] = $order;
            $order->setTravel($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): self
    {
        if ($this->orders->contains($order)) {
            $this->orders->removeElement($order);
            // set the owning side to null (unless already changed)
            if ($order->getTravel() === $this) {
                $order->setTravel(null);
            }
        }

        return $this;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(?Country $country): self
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return Collection|Flight[]
     */
    public function getFlights(): Collection
    {
        return $this->flights;
    }

    public function addFlight(Flight $flight): self
    {
        if (!$this->flights->contains($flight)) {
            $this->flights[] = $flight;
        }

        return $this;
    }

    public function removeFlight(Flight $flight): self
    {
        if ($this->flights->contains($flight)) {
            $this->flights->removeElement($flight);
        }

        return $this;
    }

    /**
     * @return Collection|Accommodation[]
     */
    public function getAccomodations(): Collection
    {
        return $this->Accomodations;
    }

    public function addAccomodation(Accommodation $accomodation): self
    {
        if (!$this->Accomodations->contains($accomodation)) {
            $this->Accomodations[] = $accomodation;
        }

        return $this;
    }

    public function removeAccomodation(Accommodation $accomodation): self
    {
        if ($this->Accomodations->contains($accomodation)) {
            $this->Accomodations->removeElement($accomodation);
        }

        return $this;
    }
}
