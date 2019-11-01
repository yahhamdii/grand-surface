<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;


/**
 * ClientDeliveryMode
 *
 * @ORM\Table(name="client_delivery_mode", uniqueConstraints={ @ORM\UniqueConstraint(name="unique_client_platform", columns={"client_id", "platform_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\ClientDeliveryModeRepository")
 * @Serializer\ExclusionPolicy("all")
 */
class ClientDeliveryMode extends AbstractEntity
{

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Client", inversedBy="clientDeliveryModes")
     * @ORM\JoinColumn(nullable=false)
     * @Serializer\MaxDepth(2)
     * @Serializer\Expose()
     */
    private $client;


    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Platform")
     * @ORM\JoinColumn(nullable=false)
     * @Serializer\MaxDepth(2)
     * @Serializer\Expose()
     */
    private $platform;


    /**
     * @ORM\Column(name="delivery_mode" ,type="boolean" ,nullable=true, options={"default": 0})
     * @Serializer\Expose()
     */
    private $deliveryMode;


    /**
     * Set deliveryMode.
     *
     * @param bool|null $deliveryMode
     *
     * @return ClientDeliveryMode
     */
    public function setDeliveryMode($deliveryMode = null)
    {
        $this->deliveryMode = $deliveryMode;

        return $this;
    }

    /**
     * Get deliveryMode.
     *
     * @return bool|null
     */
    public function getDeliveryMode()
    {
        return $this->deliveryMode;
    }

    /**
     * Set dateCreate.
     *
     * @param \DateTime|null $dateCreate
     *
     * @return ClientDeliveryMode
     */
    public function setDateCreate($dateCreate = null)
    {
        $this->dateCreate = $dateCreate;

        return $this;
    }

    /**
     * Get dateCreate.
     *
     * @return \DateTime|null
     */
    public function getDateCreate()
    {
        return $this->dateCreate;
    }

    /**
     * Set dateUpdate.
     *
     * @param \DateTime|null $dateUpdate
     *
     * @return ClientDeliveryMode
     */
    public function setDateUpdate($dateUpdate = null)
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    /**
     * Get dateUpdate.
     *
     * @return \DateTime|null
     */
    public function getDateUpdate()
    {
        return $this->dateUpdate;
    }

    /**
     * Set client.
     *
     * @param \App\Entity\Client $client
     *
     * @return ClientDeliveryMode
     */
    public function setClient(\App\Entity\Client $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client.
     *
     * @return \App\Entity\Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set platform.
     *
     * @param \App\Entity\Platform $platform
     *
     * @return ClientDeliveryMode
     */
    public function setPlatform(\App\Entity\Platform $platform)
    {
        $this->platform = $platform;

        return $this;
    }

    /**
     * Get platform.
     *
     * @return \App\Entity\Platform
     */
    public function getPlatform()
    {
        return $this->platform;
    }

}
