<?php

namespace Netrunnerdb\CardsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cycle
 */
class Cycle
{
    /**
     * @var integer
     */
    private $id;

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
     * @var integer
     */
    private $number;


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
     * Set code
     *
     * @param string $code
     * @return Cycle
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
     * @return Cycle
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
     * @return Cycle
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
     * @return Cycle
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
     * @return Cycle
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
     * @return Cycle
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
     * Set number
     *
     * @param integer $number
     * @return Cycle
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
    private $packs;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->packs = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add packs
     *
     * @param \Netrunnerdb\CardsBundle\Entity\Pack $packs
     * @return Cycle
     */
    public function addPack(\Netrunnerdb\CardsBundle\Entity\Pack $packs)
    {
        $this->packs[] = $packs;
    
        return $this;
    }

    /**
     * Remove packs
     *
     * @param \Netrunnerdb\CardsBundle\Entity\Pack $packs
     */
    public function removePack(\Netrunnerdb\CardsBundle\Entity\Pack $packs)
    {
        $this->packs->removeElement($packs);
    }

    /**
     * Get packs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPacks()
    {
        return $this->packs;
    }
}