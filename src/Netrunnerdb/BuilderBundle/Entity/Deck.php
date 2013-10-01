<?php

namespace Netrunnerdb\BuilderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Deck
 */
class Deck
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var \DateTime
     */
    private $creation;

    /**
     * @var \DateTime
     */
    private $lastupdate;

    /**
     * @var string
     */
    private $description;
    
    /**
     * @var string
     */
    private $problem;
    
    /**
     * @var integer
     */
    private $deckSize;

    /**
     * @var integer
     */
    private $influenceSpent;

    /**
     * @var integer
     */
    private $agendaPoints;

    private $message;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $slots;

    /**
     * @var \Netrunnerdb\UserBundle\Entity\User
     */
    private $user;

    /**
     * @var \Netrunnerdb\CardsBundle\Entity\Side
     */
    private $side;

    /**
     * @var Netrunnerdb\CardsBundle\Entity\Card
     */
    private $identity;
    
    /**
     * @var Netrunnerdb\CardsBundle\Entity\Pack
     */
    private $lastPack;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->slots = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Deck
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set creation
     *
     * @param \DateTime $creation
     * @return Deck
     */
    public function setCreation($creation)
    {
        $this->creation = $creation;
    
        return $this;
    }

    /**
     * Get creation
     *
     * @return \DateTime 
     */
    public function getCreation()
    {
        return $this->creation;
    }

    /**
     * Set lastupdate
     *
     * @param \DateTime $lastupdate
     * @return Deck
     */
    public function setLastupdate($lastupdate)
    {
        $this->lastupdate = $lastupdate;
    
        return $this;
    }

    /**
     * Get lastupdate
     *
     * @return \DateTime 
     */
    public function getLastupdate()
    {
        return $this->lastupdate;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return List
     */
    public function setDescription($description)
    {
    	$this->description = $description;
    
    	return $this;
    }
    
    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
    	return $this->description;
    }
    
    /**
     * Set problem
     *
     * @param string $problem
     * @return Deck
     */
    public function setProblem($problem)
    {
        $this->problem = $problem;
    
        return $this;
    }

    /**
     * Get problem
     *
     * @return string 
     */
    public function getProblem()
    {
        return $this->problem;
    }

    /**
     * Add slots
     *
     * @param \Netrunnerdb\BuilderBundle\Entity\Deckslot $slots
     * @return Deck
     */
    public function addSlot(\Netrunnerdb\BuilderBundle\Entity\Deckslot $slots)
    {
        $this->slots[] = $slots;
    
        return $this;
    }

    /**
     * Remove slots
     *
     * @param \Netrunnerdb\BuilderBundle\Entity\Deckslot $slots
     */
    public function removeSlot(\Netrunnerdb\BuilderBundle\Entity\Deckslot $slots)
    {
        $this->slots->removeElement($slots);
    }

    /**
     * Get slots
     *
     * @return \Netrunnerdb\BuilderBundle\Entity\Deckslot[] 
     */
    public function getSlots()
    {
        return $this->slots;
    }

    /**
     * Set user
     *
     * @param \Netrunnerdb\UserBundle\Entity\User $user
     * @return Deck
     */
    public function setUser(\Netrunnerdb\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return \Netrunnerdb\UserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set side
     *
     * @param \Netrunnerdb\CardsBundle\Entity\Side $side
     * @return Deck
     */
    public function setSide(\Netrunnerdb\CardsBundle\Entity\Side $side = null)
    {
        $this->side = $side;
    
        return $this;
    }

    /**
     * Get side
     *
     * @return \Netrunnerdb\CardsBundle\Entity\Side 
     */
    public function getSide()
    {
        return $this->side;
    }

    /**
     * Set identity
     *
     * @param \Netrunnerdb\CardsBundle\Entity\Card $identity
     * @return Deck
     */
    public function setIdentity($identity)
    {
    	$this->identity = $identity;
    
    	return $this;
    }
    
    /**
     * Get identity
     *
     * @return \Netrunnerdb\CardsBundle\Entity\Card
     */
    public function getIdentity()
    {
    	return $this->identity;
    }

    /**
     * Set lastPack
     *
     * @param \Netrunnerdb\CardsBundle\Entity\Pack $lastPack
     * @return Deck
     */
    public function setLastPack($lastPack)
    {
    	$this->lastPack = $lastPack;
    
    	return $this;
    }
    
    /**
     * Get lastPack
     *
     * @return \Netrunnerdb\CardsBundle\Entity\Pack
     */
    public function getLastPack()
    {
    	return $this->lastPack;
    }
    
    /**
     * Set deckSize
     *
     * @param integer $deckSize
     * @return Deck
     */
    public function setDeckSize($deckSize)
    {
        $this->deckSize = $deckSize;
    
        return $this;
    }

    /**
     * Get deckSize
     *
     * @return integer 
     */
    public function getDeckSize()
    {
        return $this->deckSize;
    }

    /**
     * Set influenceSpent
     *
     * @param integer $influenceSpent
     * @return Deck
     */
    public function setInfluenceSpent($influenceSpent)
    {
        $this->influenceSpent = $influenceSpent;
    
        return $this;
    }

    /**
     * Get influenceSpent
     *
     * @return integer 
     */
    public function getInfluenceSpent()
    {
        return $this->influenceSpent;
    }

    /**
     * Set agendaPoints
     *
     * @param integer $agendaPoints
     * @return Deck
     */
    public function setAgendaPoints($agendaPoints)
    {
        $this->agendaPoints = $agendaPoints;
    
        return $this;
    }

    /**
     * Get agendaPoints
     *
     * @return integer 
     */
    public function getAgendaPoints()
    {
        return $this->agendaPoints;
    }
    
    /**
     * Get cards
     *
     * @return Cards[]
     */
    public function getCards()
    {
    	$arr = array();
    	foreach($this->slots as $slot) {
    		$card = $slot->getCard();
    		$arr[$card->getCode()] = array('qty' => $slot->getQuantity(), 'card' => $card);
    	}
    	return $arr;
    }

    public function getContent()
    {
    	$arr = array();
    	foreach($this->slots as $slot) {
    		$arr[$slot->getCard()->getCode()] = $slot->getQuantity();
    	}
    	ksort($arr);
    	return $arr;
    }
    
    public function getMessage()
    {
    	return $this->message;
    }
    public function setMessage($message)
    {
    	$this->message = $message;
    	return $this;
    }
}