<?php

namespace Netrunnerdb\BuilderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Netrunnerdb\UserBundle\Entity\User;
use Netrunnerdb\BuilderBundle\Entity\Decklistslot;
use Netrunnerdb\BuilderBundle\Entity\Comment;

/**
 * Decklist
 */
class Decklist
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
     * @var string
     */
    private $summary;

    /**
     * @var string
     */
    private $description;

    /**
     * @var \DateTime
     */
    private $creation;
    
    /**
     * @var string
     */
    private $signature;

    /**
     * @var Netrunnerdb\UserBundle\Entity\User
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
     * @var Netrunnerdb\CardsBundle\Entity\Faction
     */
    private $faction;
    
    /**
     * @var Netrunnerdb\CardsBundle\Entity\Pack
     */
    private $lastPack;
    
    /**
     * @var Deckslots[]
     */
    private $slots;
    
    /**
     * @var Comments[]
     */
    private $comments;
    
    /**
     * @var User[]
     */
    private $favorites;

    /**
     * @var User[]
     */
    private $votes;
    
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
     * @return List
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
     * Set summary
     *
     * @param string $summary
     * @return List
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;
    
        return $this;
    }

    /**
     * Get summary
     *
     * @return string 
     */
    public function getSummary()
    {
        return $this->summary;
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
     * Set creation
     *
     * @param \DateTime $creation
     * @return List
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
     * Set signature
     *
     * @param string $signature
     * @return Decklist
     */
    public function setSignature($signature)
    {
        $this->signature = $signature;
    
        return $this;
    }

    /**
     * Get signature
     *
     * @return string 
     */
    public function getSignature()
    {
        return $this->signature;
    }
    
    /**
     * Set user
     *
     * @param string $user
     * @return User
     */
    public function setUser($user)
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
     * Set slots
     *
     * @param string $slots
     * @return Deck
     */
    public function setSlots($slots)
    {
    	$this->slots = $slots;
    
    	return $this;
    }
    
    /**
     * Get slots
     *
     * @return string
     */
    public function getSlots()
    {
    	return $this->slots;
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
     * Set faction
     *
     * @param \Netrunnerdb\CardsBundle\Entity\Faction $faction
     * @return Deck
     */
    public function setFaction($faction)
    {
    	$this->faction = $faction;
    
    	return $this;
    }
    
    /**
     * Get faction
     *
     * @return \Netrunnerdb\CardsBundle\Entity\Faction
     */
    public function getFaction()
    {
    	return $this->faction;
    }
    

    /**
     * Set comments
     *
     * @param string $comments
     * @return Deck
     */
    public function setComments($comments)
    {
    	$this->comments = $comments;
    
    	return $this;
    }
    
    /**
     * Get comments
     *
     * @return string
     */
    public function getComments()
    {
    	return $this->comments;
    }

    /**
     * Add favorite
     *
     * @param User $user
     * @return Decklist
     */
    public function addFavorite($user)
    {
    	$this->favorites[] = $user;
    
    	return $this;
    }
    
    /**
     * Get favorites
     *
     * @return User[]
     */
    public function getFavorites()
    {
    	return $this->favorites;
    }

    /**
     * Add vote
     *
     * @param User $user
     * @return Decklist
     */
    public function addVote($user)
    {
    	$this->votes[] = $user;
    
    	return $this;
    }
    
    /**
     * Get votes
     *
     * @return User[]
     */
    public function getVotes()
    {
    	return $this->votes;
    }
    
    public function __construct()
    {
    	$this->slots = new ArrayCollection();
    	$this->comments = new ArrayCollection();
      	$this->favorites = new ArrayCollection();
       	$this->votes = new ArrayCollection();
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
    
    public function getPrettyName()
    {
    	return preg_replace('/[^a-z0-9]+/', '-', strtolower($this->name));
    }

    /**
     * Add slots
     *
     * @param \Netrunnerdb\BuilderBundle\Entity\Decklistslot $slots
     * @return Decklist
     */
    public function addSlot(\Netrunnerdb\BuilderBundle\Entity\Decklistslot $slots)
    {
        $this->slots[] = $slots;
    
        return $this;
    }

    /**
     * Remove slots
     *
     * @param \Netrunnerdb\BuilderBundle\Entity\Decklistslot $slots
     */
    public function removeSlot(\Netrunnerdb\BuilderBundle\Entity\Decklistslot $slots)
    {
        $this->slots->removeElement($slots);
    }

    /**
     * Add comments
     *
     * @param \Netrunnerdb\BuilderBundle\Entity\Comment $comments
     * @return Decklist
     */
    public function addComment(\Netrunnerdb\BuilderBundle\Entity\Comment $comments)
    {
        $this->comments[] = $comments;
    
        return $this;
    }

    /**
     * Remove comments
     *
     * @param \Netrunnerdb\BuilderBundle\Entity\Comment $comments
     */
    public function removeComment(\Netrunnerdb\BuilderBundle\Entity\Comment $comments)
    {
        $this->comments->removeElement($comments);
    }

    /**
     * Remove favorites
     *
     * @param \Netrunnerdb\UserBundle\Entity\User $favorites
     */
    public function removeFavorite(\Netrunnerdb\UserBundle\Entity\User $favorites)
    {
        $this->favorites->removeElement($favorites);
    }

    /**
     * Remove votes
     *
     * @param \Netrunnerdb\UserBundle\Entity\User $votes
     */
    public function removeVote(\Netrunnerdb\UserBundle\Entity\User $votes)
    {
        $this->votes->removeElement($votes);
    }
    /**
     * @var \Netrunnerdb\BuilderBundle\Entity\Deck
     */
    private $parent;


    /**
     * Set parent
     *
     * @param \Netrunnerdb\BuilderBundle\Entity\Deck $parent
     * @return Decklist
     */
    public function setParent(\Netrunnerdb\BuilderBundle\Entity\Deck $parent = null)
    {
        $this->parent = $parent;
    
        return $this;
    }

    /**
     * Get parent
     *
     * @return \Netrunnerdb\BuilderBundle\Entity\Deck 
     */
    public function getParent()
    {
        return $this->parent;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $successors;

    /**
     * @var \Netrunnerdb\BuilderBundle\Entity\Decklist
     */
    private $precedent;


    /**
     * Add successors
     *
     * @param \Netrunnerdb\BuilderBundle\Entity\Decklist $successors
     * @return Decklist
     */
    public function addSuccessor(\Netrunnerdb\BuilderBundle\Entity\Decklist $successors)
    {
        $this->successors[] = $successors;
    
        return $this;
    }

    /**
     * Remove successors
     *
     * @param \Netrunnerdb\BuilderBundle\Entity\Decklist $successors
     */
    public function removeSuccessor(\Netrunnerdb\BuilderBundle\Entity\Decklist $successors)
    {
        $this->successors->removeElement($successors);
    }

    /**
     * Get successors
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSuccessors()
    {
        return $this->successors;
    }

    /**
     * Set precedent
     *
     * @param \Netrunnerdb\BuilderBundle\Entity\Decklist $precedent
     * @return Decklist
     */
    public function setPrecedent(\Netrunnerdb\BuilderBundle\Entity\Decklist $precedent = null)
    {
        $this->precedent = $precedent;
    
        return $this;
    }

    /**
     * Get precedent
     *
     * @return \Netrunnerdb\BuilderBundle\Entity\Decklist 
     */
    public function getPrecedent()
    {
        return $this->precedent;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $children;


    /**
     * Add children
     *
     * @param \Netrunnerdb\BuilderBundle\Entity\Deck $children
     * @return Decklist
     */
    public function addChildren(\Netrunnerdb\BuilderBundle\Entity\Deck $children)
    {
        $this->children[] = $children;
    
        return $this;
    }

    /**
     * Remove children
     *
     * @param \Netrunnerdb\BuilderBundle\Entity\Deck $children
     */
    public function removeChildren(\Netrunnerdb\BuilderBundle\Entity\Deck $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getChildren()
    {
        return $this->children;
    }
}