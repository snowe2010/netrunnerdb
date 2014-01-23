<?php

namespace Netrunnerdb\CardsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Netrunnerdb\UserBundle\Entity\User;
use Netrunnerdb\CardsBundle\Entity\Card;

/**
 * Opinion
 */
class Opinion
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $text;

    /**
     * @var \DateTime
     */
    private $creation;

    /**
     * @var User
     */
    private $author;

    /**
     * @var Card
     */
    private $card;

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
     * @param string $text
     * @return Opinion
     */
    public function setText($text)
    {
        $this->text = $text;
    
        return $this;
    }

    /**
     * Get text
     *
     * @return string 
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set creation
     *
     * @param \DateTime $creation
     * @return Opinion
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
     * Set author
     *
     * @param string $author
     * @return User
     */
    public function setAuthor($author)
    {
    	$this->author = $author;
    
    	return $this;
    }
    
    /**
     * Get author
     *
     * @return string
     */
    public function getAuthor()
    {
    	return $this->author;
    }

    /**
     * Set card
     *
     * @param string $card
     * @return Opinion
     */
    public function setCard($card)
    {
    	$this->card = $card;
    
    	return $this;
    }
    
    /**
     * Get card
     *
     * @return Card
     */
    public function getCard()
    {
    	return $this->card;
    }
}
