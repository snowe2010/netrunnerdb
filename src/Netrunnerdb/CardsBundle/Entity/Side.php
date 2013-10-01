<?php

namespace Netrunnerdb\CardsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Side
 */
class Side
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
    private $nameFr;

    /**
     * @var string
     */
    private $nameDe;

    /**
     * @var string
     */
    private $nameEs;

    /**
     * @var string
     */
    private $namePl;


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
     * Set text
     *
     * @param string $name
     * @return Side
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get text
     *
     * @return string 
     */
    public function getName($locale = "en")
    {
    	$res = $this->name;
    	if($locale == "fr") $res = $this->nameFr ?: $res;
    	if($locale == "de") $res = $this->nameDe ?: $res;
    	if($locale == "es") $res = $this->nameEs ?: $res;
    	if($locale == "pl") $res = $this->namePl ?: $res;
    	return $res;
    }

    /**
     * Set textFr
     *
     * @param string $nameFr
     * @return Side
     */
    public function setNameFr($nameFr)
    {
        $this->nameFr = $nameFr;
    
        return $this;
    }

    /**
     * Get textFr
     *
     * @return string 
     */
    public function getNameFr()
    {
        return $this->nameFr;
    }

    /**
     * Set textDe
     *
     * @param string $nameDe
     * @return Side
     */
    public function setNameDe($nameDe)
    {
        $this->nameDe = $nameDe;
    
        return $this;
    }

    /**
     * Get textDe
     *
     * @return string 
     */
    public function getNameDe()
    {
        return $this->nameDe;
    }

    /**
     * Set textEs
     *
     * @param string $nameEs
     * @return Side
     */
    public function setNameEs($nameEs)
    {
        $this->nameEs = $nameEs;
    
        return $this;
    }

    /**
     * Get textEs
     *
     * @return string 
     */
    public function getNameEs()
    {
        return $this->nameEs;
    }

    /**
     * Set textPl
     *
     * @param string $namePl
     * @return Side
     */
    public function setNamePl($namePl)
    {
        $this->namePl = $namePl;
    
        return $this;
    }

    /**
     * Get textPl
     *
     * @return string 
     */
    public function getNamePl()
    {
        return $this->namePl;
    }

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $cards;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $factions;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $decks;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $decklists;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->cards = new \Doctrine\Common\Collections\ArrayCollection();
        $this->factions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->decks = new \Doctrine\Common\Collections\ArrayCollection();
        $this->decklists = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add cards
     *
     * @param \Netrunnerdb\CardsBundle\Entity\Card $cards
     * @return Side
     */
    public function addCard(\Netrunnerdb\CardsBundle\Entity\Card $cards)
    {
        $this->cards[] = $cards;
    
        return $this;
    }

    /**
     * Remove cards
     *
     * @param \Netrunnerdb\CardsBundle\Entity\Card $cards
     */
    public function removeCard(\Netrunnerdb\CardsBundle\Entity\Card $cards)
    {
        $this->cards->removeElement($cards);
    }

    /**
     * Get cards
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCards()
    {
        return $this->cards;
    }

    /**
     * Add decks
     *
     * @param \Netrunnerdb\BuilderBundle\Entity\Deck $decks
     * @return Side
     */
    public function addDeck(\Netrunnerdb\BuilderBundle\Entity\Deck $decks)
    {
        $this->decks[] = $decks;
    
        return $this;
    }

    /**
     * Remove decks
     *
     * @param \Netrunnerdb\BuilderBundle\Entity\Deck $decks
     */
    public function removeDeck(\Netrunnerdb\BuilderBundle\Entity\Deck $decks)
    {
        $this->decks->removeElement($decks);
    }

    /**
     * Get decks
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDecks()
    {
        return $this->decks;
    }
    
    /**
     * Get decklists
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDecklists()
    {
        return $this->decklists;
    }

    /**
     * Get decklists
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFactions()
    {
    	return $this->factions;
    }
}