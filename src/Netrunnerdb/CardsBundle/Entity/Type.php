<?php

namespace Netrunnerdb\CardsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Type
 */
class Type
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
     * @return Type
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
     * @return Type
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
     * @return Type
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
     * @return Type
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
     * @return Type
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
     * Constructor
     */
    public function __construct()
    {
        $this->cards = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add cards
     *
     * @param \Netrunnerdb\CardsBundle\Entity\Card $cards
     * @return Type
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
}