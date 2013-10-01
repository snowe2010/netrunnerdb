<?php

namespace Netrunnerdb\CardsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Pack
 */
class Pack
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $ts;

    /**
     * @var string
     */
    private $code;

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
     * @var \DateTime
     */
    private $released;

    /**
     * @var integer
     */
    private $size;

    /**
     * @var integer
     */
    private $number;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $decklists;

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
     * Set ts
     *
     * @param \DateTime $ts
     * @return Card
     */
    public function setTs($ts)
    {
        $this->ts = $ts;
    
        return $this;
    }

    /**
     * Get ts
     *
     * @return \DateTime 
     */
    public function getTs()
    {
        return $this->ts;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return Pack
     */
    public function setCode($code)
    {
        $this->code = $code;
    
        return $this;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Pack
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
     * Set nameFr
     *
     * @param string $nameFr
     * @return Pack
     */
    public function setNameFr($nameFr)
    {
        $this->nameFr = $nameFr;
    
        return $this;
    }

    /**
     * Get nameFr
     *
     * @return string 
     */
    public function getNameFr()
    {
        return $this->nameFr;
    }

    /**
     * Set nameDe
     *
     * @param string $nameDe
     * @return Pack
     */
    public function setNameDe($nameDe)
    {
        $this->nameDe = $nameDe;
    
        return $this;
    }

    /**
     * Get nameDe
     *
     * @return string 
     */
    public function getNameDe()
    {
        return $this->nameDe;
    }

    /**
     * Set nameEs
     *
     * @param string $nameEs
     * @return Pack
     */
    public function setNameEs($nameEs)
    {
        $this->nameEs = $nameEs;
    
        return $this;
    }

    /**
     * Get nameEs
     *
     * @return string 
     */
    public function getNameEs()
    {
        return $this->nameEs;
    }

    /**
     * Set namePl
     *
     * @param string $namePl
     * @return Pack
     */
    public function setNamePl($namePl)
    {
        $this->namePl = $namePl;
    
        return $this;
    }

    /**
     * Get namePl
     *
     * @return string 
     */
    public function getNamePl()
    {
        return $this->namePl;
    }

    /**
     * Set released
     *
     * @param \DateTime $released
     * @return Pack
     */
    public function setReleased($released)
    {
        $this->released = $released;
    
        return $this;
    }

    /**
     * Get released
     *
     * @return \DateTime 
     */
    public function getReleased()
    {
        return $this->released;
    }

    /**
     * Set size
     *
     * @param integer $size
     * @return Pack
     */
    public function setSize($size)
    {
        $this->size = $size;
    
        return $this;
    }

    /**
     * Get size
     *
     * @return integer 
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set number
     *
     * @param integer $number
     * @return Card
     */
    public function setNumber($number)
    {
        $this->number = $number;
    
        return $this;
    }

    /**
     * Get number
     *
     * @return integer 
     */
    public function getNumber()
    {
        return $this->number;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $cards;

    /**
     * @var \Netrunnerdb\CardsBundle\Entity\Cycle
     */
    private $cycle;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->ts = new \DateTime(); 
    	$this->cards = new \Doctrine\Common\Collections\ArrayCollection();
    	$this->decklists = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add cards
     *
     * @param \Netrunnerdb\CardsBundle\Entity\Card $cards
     * @return Pack
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
     * Set cycle
     *
     * @param \Netrunnerdb\CardsBundle\Entity\Cycle $cycle
     * @return Pack
     */
    public function setCycle(\Netrunnerdb\CardsBundle\Entity\Cycle $cycle = null)
    {
        $this->cycle = $cycle;
    
        return $this;
    }

    /**
     * Get cycle
     *
     * @return \Netrunnerdb\CardsBundle\Entity\Cycle 
     */
    public function getCycle()
    {
        return $this->cycle;
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
}