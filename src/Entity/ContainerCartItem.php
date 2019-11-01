<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * ContainerCartItem
 *
 * @ORM\Table(name="container_cartItem")
 * @ORM\Entity(repositoryClass="App\Repository\ContainerCartItemRepository")
 * @Serializer\ExclusionPolicy("all")
 */
class ContainerCartItem extends AbstractEntity
{

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Container", inversedBy="containerCartItems")
     * @ORM\JoinColumn(nullable= false)
     */
    private $container;


    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CartItem", inversedBy="containerCartItems")
     * @ORM\JoinColumn(nullable= false)
     */
    private $cartItem;

    /**
     * @var int
     *
     * @ORM\Column(name="quantity", type="integer")
     */
    private $quantity;


    /**
     * Set quantity.
     *
     * @param int $quantity
     *
     * @return ContainerCartItem
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity.
     *
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set dateCreate.
     *
     * @param \DateTime|null $dateCreate
     *
     * @return ContainerCartItem
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
     * @return ContainerCartItem
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
     * Set container.
     *
     * @param \App\Entity\Container $container
     *
     * @return ContainerCartItem
     */
    public function setContainer(\App\Entity\Container $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Get container.
     *
     * @return \App\Entity\Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Set cartItem.
     *
     * @param \App\Entity\CartItem $cartItem
     *
     * @return ContainerCartItem
     */
    public function setCartItem(\App\Entity\CartItem $cartItem)
    {
        $this->cartItem = $cartItem;

        return $this;
    }

    /**
     * Get cartItem.
     *
     * @return \App\Entity\CartItem
     */
    public function getCartItem()
    {
        return $this->cartItem;
    }


    /**
     * @return int|null
     */
    public function getVolumePackageLoaded($quantity = null)
    {
        if(is_null($quantity)){
            $quantity = $this->getQuantity();
        }
        $volumePackage = $this->getCartItem()->getItem()->getPackage()->getVolumePackage();
        if(strlen($volumePackage) == 0){
            $item = $this->getCartItem()->getItem();
            $pcb = $item->getPcb();
            $volumeUnit = $item->getPackage()->getVolumeUc();

            //TODO a verifier unite de mesure pour volume unit soit cm3 soit m3 pout savoir si on va diviser par 100 ou nn
            $volumePackage = ($volumeUnit * $pcb) / 100;
        }

        return $quantity * $volumePackage;
    }

    /**
     * @return int|null
     */
    public function getWeightPackageLoaded($quantity = null)
    {
        if(is_null($quantity)){
            $quantity = $this->getQuantity();
        }
        $weightPackage = $this->getCartItem()->getItem()->getPackage()->getWeightGrossPackage();
        if(isset($weightPackage)){

            return $quantity * $weightPackage;
        }

        return null;
    }
}
