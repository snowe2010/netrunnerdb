<?php

namespace Netrunnerdb\CardsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Changelog
 */
class Changelog
{
    /**
     * @var string
     */
    private $date;

    /**
     * Get date
     *
     * @return string 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @var string
     */
    private $change;

    /**
     * Get change
     *
     * @return string 
     */
    public function getChange()
    {
        return $this->change;
    }
}