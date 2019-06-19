<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OrderRepository")
 */
class Order
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Client", inversedBy="orders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $client_id;

    /**
     * @ORM\Column(type="smallint")
     */
    private $airport_transfer;

    /**
     * @ORM\Column(type="boolean")
     */
    private $cancel_insurance;

    /**
     * @ORM\Column(type="boolean")
     */
    private $option_1;

    /**
     * @ORM\Column(type="boolean")
     */
    private $option_2;

    /**
     * @ORM\Column(type="boolean")
     */
    private $option_3;

    /**
     * @ORM\Column(type="integer")
     */
    private $total_price;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Tour", inversedBy="orders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $travel;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClientId(): ?Client
    {
        return $this->client_id;
    }

    public function setClientId(?Client $client_id): self
    {
        $this->client_id = $client_id;

        return $this;
    }

    public function getAirportTransfer(): ?int
    {
        return $this->airport_transfer;
    }

    public function setAirportTransfer(int $airport_transfer): self
    {
        $this->airport_transfer = $airport_transfer;

        return $this;
    }

    public function getCancelInsurance(): ?bool
    {
        return $this->cancel_insurance;
    }

    public function setCancelInsurance(bool $cancel_insurance): self
    {
        $this->cancel_insurance = $cancel_insurance;

        return $this;
    }

    public function getOption1(): ?bool
    {
        return $this->option_1;
    }

    public function setOption1(bool $option_1): self
    {
        $this->option_1 = $option_1;

        return $this;
    }

    public function getOption2(): ?bool
    {
        return $this->option_2;
    }

    public function setOption2(bool $option_2): self
    {
        $this->option_2 = $option_2;

        return $this;
    }

    public function getOption3(): ?bool
    {
        return $this->option_3;
    }

    public function setOption3(bool $option_3): self
    {
        $this->option_3 = $option_3;

        return $this;
    }

    public function getTotalPrice(): ?int
    {
        return $this->total_price;
    }

    public function setTotalPrice(int $total_price): self
    {
        $this->total_price = $total_price;

        return $this;
    }

    public function getTravel(): ?Tour
    {
        return $this->travel;
    }

    public function setTravel(?Tour $travel): self
    {
        $this->travel = $travel;

        return $this;
    }
}
