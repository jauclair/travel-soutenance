<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FlightRepository")
 */
class Flight
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $company_name;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $flight_number;

    /**
     * @ORM\Column(type="string", length=6)
     */
    private $icao24;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $departure_airport;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $arrival_airport;

    /**
     * @ORM\Column(type="date")
     */
    private $departure_date;

    /**
     * @ORM\Column(type="date")
     */
    private $arrival_date;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Tour", mappedBy="flights")
     */
    private $tours;

    public function __construct()
    {
        $this->tours = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompanyName(): ?string
    {
        return $this->company_name;
    }

    public function setCompanyName(string $company_name): self
    {
        $this->company_name = $company_name;

        return $this;
    }

    public function getFlightNumber(): ?string
    {
        return $this->flight_number;
    }

    public function setFlightNumber(string $flight_number): self
    {
        $this->flight_number = $flight_number;

        return $this;
    }

    public function getIcao24(): ?string
    {
        return $this->icao24;
    }

    public function setIcao24(string $icao24): self
    {
        $this->icao24 = $icao24;

        return $this;
    }

    public function getDepartureAirport(): ?string
    {
        return $this->departure_airport;
    }

    public function setDepartureAirport(string $departure_airport): self
    {
        $this->departure_airport = $departure_airport;

        return $this;
    }

    public function getArrivalAirport(): ?string
    {
        return $this->arrival_airport;
    }

    public function setArrivalAirport(string $arrival_airport): self
    {
        $this->arrival_airport = $arrival_airport;

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

    /**
     * @return Collection|Tour[]
     */
    public function getTours(): Collection
    {
        return $this->tours;
    }

    public function addTour(Tour $tour): self
    {
        if (!$this->tours->contains($tour)) {
            $this->tours[] = $tour;
            $tour->addFlight($this);
        }

        return $this;
    }

    public function removeTour(Tour $tour): self
    {
        if ($this->tours->contains($tour)) {
            $this->tours->removeElement($tour);
            $tour->removeFlight($this);
        }

        return $this;
    }
}
