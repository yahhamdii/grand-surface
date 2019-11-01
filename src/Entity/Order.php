<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Order
 *
 * @ORM\Table(name="`order`")
 * @ORM\Entity(repositoryClass="App\Repository\OrderRepository")
 * @Serializer\ExclusionPolicy("all")
 */
class Order extends AbstractEntity
{

    const STATUS_PENDING_VALIDATION = 'STATUS_PENDING';
    const STATUS_APPROVED = 'STATUS_APPROVED';
    const STATUS_PROCESSED = 'STATUS_PROCESSED';
    const STATUS_PENDING_PREPARE = 'STATUS_PENDING_PREPARE';
    const STATUS_INVOICED = 'STATUS_INVOICED';
    const STATUS_REJECTED = 'STATUS_REJECTED';
    const STATUS_ORDER_TO_BE_REVALIDATE = 'STATUS_ORDER_TO_BE_REVALIDATE';
    const STATUS_ORDER_DELIVERED = 'STATUS_ORDER_DELIVERED';


    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     * @Serializer\Expose
     * @Serializer\MaxDepth(1)
     */
    private $user;


    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @Serializer\Expose
     * @Serializer\MaxDepth(1)
     */
    private $validator;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Platform")
     * @ORM\JoinColumn(nullable=false)
     */
    private $platform;


    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Historique", mappedBy="order", cascade={"persist", "remove"})
     */
    private $historiques;


    /**
     * @ORM\Column(name="slug", type="string", length=255, nullable=true, unique=true)
     * @Serializer\Expose
     */
    private $slug;

    /**
     * @ORM\Column(name="date_validate", type="datetime", nullable=true)
     * @Serializer\Expose
     * @Serializer\Groups({"list"})
     */
    private $dateValidate;

    /**
     * @ORM\Column(name="date_delivery", type="datetime", nullable=true)
     * @Serializer\Expose
     */
    private $dateDelivery;

    /**
     * @ORM\Column(name="preorder_date_end", type="datetime", nullable=true)
     * @Serializer\Expose
     */
    private $preOrderDateEnd;

    /**
     * @ORM\Column(name="preorder_delivery_date", type="datetime", nullable=true)
     * @Serializer\Expose
     */
    private $preOrderDeliveryDate;

    /**
     * @ORM\Column(name="is_preorder", type="boolean")
     * @Serializer\Expose
     */
    private $isPreorder = false;

    /**
     * @ORM\Column(name="number", type="string", length=255, nullable=true)
     * @Serializer\Expose
     */
    private $number;

    /**
     * @ORM\Column(name="total_price", type="decimal", precision=8, scale=2, nullable=true)
     * @Serializer\Expose
     */
    private $totalPrice;

    /**
     * @ORM\Column(name="total_price_vat", type="decimal", precision=8, scale=2, nullable=true)
     * @Serializer\Expose
     */
    private $totalPriceVat;

    /**
     * @ORM\Column(name="comment", type="text", nullable=true)
     * @Serializer\Expose
     */
    private $comment;

    /**
     * @ORM\Column(name="volume", type="string", length=255, nullable=true)
     * @Serializer\Expose
     */
    private $volume;


    /**
     * @ORM\Column(name="weight", type="string", length=255, nullable=true)
     * @Serializer\Expose
     */
    private $weight;

    /**
     * @ORM\Column(name="file_generated", type="boolean")
     * @Serializer\Expose
     */
    private $fileGenerated = false;

    /**
     * @ORM\ManyToOne(targetEntity="Status")
     * @ORM\JoinColumn(nullable=false)
     * @Serializer\Expose
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity="OrderItem", mappedBy="order", cascade={"remove"})
     * @Serializer\Expose
     * @Serializer\MaxDepth(2)
     */
    private $orderItems;
    
    /**
     * @ORM\OneToMany(targetEntity="Invoice", mappedBy="order")
     * @Serializer\Expose
     * @Serializer\MaxDepth(2)
     */
    private $invoicesOrder;


    /**
     * @ORM\Column(name="delivery_mode" ,type="boolean" ,nullable=true, options={"default": 0})
     * @Serializer\Expose()
     */
    private $deliveryMode;


    /**
     * @ORM\Column(name="delivery_amount", type="decimal", precision=8, scale=2, nullable=true)
     * @Serializer\Expose
     */
    private $deliveryAmount;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->historiques = new \Doctrine\Common\Collections\ArrayCollection();        
    }
    

    /**
     * Set slug.
     *
     * @param string|null $slug
     *
     * @return Order
     */
    public function setSlug($slug = null)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug.
     *
     * @return string|null
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set dateValidate.
     *
     * @param \DateTime|null $dateValidate
     *
     * @return Order
     */
    public function setDateValidate($dateValidate = null)
    {
        $this->dateValidate = $dateValidate;

        return $this;
    }

    /**
     * Get dateValidate.
     *
     * @return \DateTime|null
     */
    public function getDateValidate()
    {
        return $this->dateValidate;
    }

    /**
     * Set dateDelivery.
     *
     * @param \DateTime|null $dateDelivery
     *
     * @return Order
     */
    public function setDateDelivery($dateDelivery = null)
    {
        $this->dateDelivery = $dateDelivery;

        return $this;
    }

    /**
     * Get dateDelivery.
     *
     * @return \DateTime|null
     */
    public function getDateDelivery()
    {
        return $this->dateDelivery;
    }

    /**
     * Set number.
     *
     * @param string|null $number
     *
     * @return Order
     */
    public function setNumber($number = null)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number.
     *
     * @return string|null
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set totalPrice.
     *
     * @param string|null $totalPrice
     *
     * @return Order
     */
    public function setTotalPrice($totalPrice = null)
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    /**
     * Get totalPrice.
     *
     * @return string|null
     */
    public function getTotalPrice()
    {
        return $this->totalPrice;
    }

    /**
     * Set comment.
     *
     * @param string|null $comment
     *
     * @return Order
     */
    public function setComment($comment = null)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment.
     *
     * @return string|null
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set volume.
     *
     * @param string|null $volume
     *
     * @return Order
     */
    public function setVolume($volume = null)
    {
        $this->volume = $volume;

        return $this;
    }

    /**
     * Get volume.
     *
     * @return string|null
     */
    public function getVolume()
    {
        return $this->volume;
    }

    /**
     * Set weight.
     *
     * @param string|null $weight
     *
     * @return Order
     */
    public function setWeight($weight = null)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * Get weight.
     *
     * @return string|null
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Set user.
     *
     * @param \App\Entity\User $user
     *
     * @return Order
     */
    public function setUser(\App\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return \App\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set validator.
     *
     * @param \App\Entity\User|null $validator
     *
     * @return Order
     */
    public function setValidator(\App\Entity\User $validator = null)
    {
        $this->validator = $validator;

        return $this;
    }

    /**
     * Get validator.
     *
     * @return \App\Entity\User|null
     */
    public function getValidator()
    {
        return $this->validator;
    }

    /**
     * Set platform.
     *
     * @param \App\Entity\Platform $platform
     *
     * @return Order
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

    /**
     * Set dateCreate.
     *
     * @param \DateTime|null $dateCreate
     *
     * @return Order
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
     * @return Order
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
     * Set status.
     *
     * @param \App\Entity\Status $status
     *get
     * @return Order
     */
    public function setStatus(\App\Entity\Status $status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return \App\Entity\Status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getFileGenerated()
    {
        return $this->fileGenerated;
    }

    /**
     * @param mixed $fileGenerated
     */
    public function setFileGenerated($fileGenerated)
    {
        $this->fileGenerated = $fileGenerated;
    }

    /**
     * Add historique.
     *
     * @param \App\Entity\Historique $historique
     *
     * @return Order
     */
    public function addHistorique(\App\Entity\Historique $historique)
    {
        $this->historiques[] = $historique;

        return $this;
    }

    /**
     * Remove historique.
     *
     * @param \App\Entity\Historique $historique
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeHistorique(\App\Entity\Historique $historique)
    {
        return $this->historiques->removeElement($historique);
    }

    /**
     * Get historiques.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getHistoriques()
    {
        return $this->historiques;
    }

    /**
     * Set totalPriceVat.
     *
     * @param string|null $totalPriceVat
     *
     * @return Order
     */
    public function setTotalPriceVat($totalPriceVat = null)
    {
        $this->totalPriceVat = $totalPriceVat;

        return $this;
    }

    /**
     * Get totalPriceVat.
     *
     * @return string|null
     */
    public function getTotalPriceVat()
    {
        return $this->totalPriceVat;
    }

    /**
     * Add orderItem.
     *
     * @param \App\Entity\OrderItem $orderItem
     *
     * @return Order
     */
    public function addOrderItem(\App\Entity\OrderItem $orderItem)
    {
        $this->orderItems[] = $orderItem;

        return $this;
    }

    /**
     * Remove orderItem.
     *
     * @param \App\Entity\OrderItem $orderItem
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeOrderItem(\App\Entity\OrderItem $orderItem)
    {
        return $this->orderItems->removeElement($orderItem);
    }

    /**
     * Get orderItems.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrderItems()
    {
        return $this->orderItems;
    }

    /**
     * @return mixed
     */
    public function getPreOrderDateEnd()
    {
        return $this->preOrderDateEnd;
    }

    /**
     * @param mixed $preOrderDateEnd
     */
    public function setPreOrderDateEnd($preOrderDateEnd)
    {
        $this->preOrderDateEnd = $preOrderDateEnd;
    }

    /**
     * @return mixed
     */
    public function getPreOrderDeliveryDate()
    {
        return $this->preOrderDeliveryDate;
    }

    /**
     * @param mixed $preOrderDeliveryDate
     */
    public function setPreOrderDeliveryDate($preOrderDeliveryDate)
    {
        $this->preOrderDeliveryDate = $preOrderDeliveryDate;
    }

    /**
     * @Serializer\VirtualProperty()
     */
    public function getTotalsByType()
    {

        $totals = [];

        if (!empty($this->orderItems)) {
            foreach ($this->orderItems as $orderItem) {
                if ($orderItem->getItemType()) {
                    if (!isset($totals[$orderItem->getItemType()])) {
                        $totals[$orderItem->getItemType()] = array(
                            'total_price_vat' => 0,
                            'total_price' => 0,
                            'total_quantity' => 0,
                        );
                    }

                    $totals[$orderItem->getItemType()]['total_price_vat'] += $orderItem->getFinalPriceVat();
                    $totals[$orderItem->getItemType()]['total_price'] += $orderItem->getFinalPrice();
                    $totals[$orderItem->getItemType()]['total_quantity'] += $orderItem->getQuantity();
                }
            }
        }

        return $totals;
    }

     /**
     * @Serializer\VirtualProperty()
     */
    public function getCountItemsByType(){

        $totals = [];

        if(!empty($this->orderItems)){
            foreach ($this->orderItems as $orderItem){
                if( $orderItem->getItem() ){
                    if( !isset($totals[ $orderItem->getItemType() ])){
                        $totals[ $orderItem->getItemType() ] = 0;
                    }

                    $totals[ $orderItem->getItemType() ] += 1;
                }
            }
        }

        return $totals;
    }

    /**
     * @Serializer\VirtualProperty()
     */
    public function getReferences()
    {

        $packages = 0;
        $units = 0;

        if (!empty($this->orderItems)) {
            foreach ($this->orderItems as $orderItem) {
                $packages += $orderItem->getQuantity();
                $units += $orderItem->getItemPcb() *  $orderItem->getQuantity();
            }
        }

        return array('packages' => $packages, 'items' => $units);
    }

    /**
     * Set isPreorder.
     *
     * @param bool $isPreorder
     *
     * @return Order
     */
    public function setIsPreorder($isPreorder)
    {
        $this->isPreorder = $isPreorder;

        return $this;
    }

    /**
     * Get isPreorder.
     *
     * @return bool
     */
    public function getIsPreorder()
    {
        return $this->isPreorder;
    }

   
    /**
     * Set deliveryMode.
     *
     * @param bool|null $deliveryMode
     *
     * @return Order
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
     * Set deliveryAmount.
     *
     * @param string|null $deliveryAmount
     *
     * @return Order
     */
    public function setDeliveryAmount($deliveryAmount = null)
    {
        $this->deliveryAmount = $deliveryAmount;

        return $this;
    }

    /**
     * Get deliveryAmount.
     *
     * @return string|null
     */
    public function getDeliveryAmount()
    {
        return $this->deliveryAmount;
    } 

     /**
     * @Serializer\VirtualProperty()
     */
    public function getHasInvoicesOrder() {

        if(!is_null($this->getInvoicesOrder())){
            
            return ($this->getInvoicesOrder()->count() > 0);
        }

        return false;
        
    }

    /**
     * Add invoicesOrder.
     *
     * @param \App\Entity\Invoice $invoicesOrder
     *
     * @return Order
     */
    public function addInvoicesOrder(\App\Entity\Invoice $invoicesOrder)
    {
        $this->invoicesOrder[] = $invoicesOrder;

        return $this;
    }

    /**
     * Remove invoicesOrder.
     *
     * @param \App\Entity\Invoice $invoicesOrder
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeInvoicesOrder(\App\Entity\Invoice $invoicesOrder)
    {
        return $this->invoicesOrder->removeElement($invoicesOrder);
    }

    /**
     * Get invoicesOrder.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getInvoicesOrder()
    {
        return $this->invoicesOrder;
    }

    /* Useless for now */
    /*public function getInvoicesItems(){

        $invoices = $this->getInvoicesOrder();
        $invoiceItems = [];

        foreach ($invoices as $key => $invoice) {
            if($invoice->getInvoiceItems()){
                $invoiceItems = array_merge($invoice->getInvoiceItems()->toArray(), $invoiceItems);
            }
        }

        return $invoiceItems;
    }*/
}
