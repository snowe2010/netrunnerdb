<?php

namespace Netrunnerdb\UserBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Netrunnerdb\BuilderBundle\Entity\Deck;
use Netrunnerdb\BuilderBundle\Entity\Decklist;
use Netrunnerdb\BuilderBundle\Entity\Comment;

/**
 * User
 */
class User extends BaseUser
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var integer
     */
    private $reputation;

    /**
     * @var string
     */
    private $faction;
    
    /**
     * @var \DateTime
     */
    private $creation;

    /**
     * @var string
     */
    private $resume;

    /**
     * @var integer
     */
    private $role;

    /**
     * @var integer
     */
    private $status;

    /**
     * @var string
     */
    private $avatar;

    /*
     * @var integer
     */
    private $donation;
    
    /**
     * @var Deck[]
     */
    private $decks;

    /**
     * @var Decklist[]
     */
    private $decklists;
    
    /**
     * @var Comments[]
     */
    private $comments;

    /**
     * @var Opinions[]
     */
    private $opinions;
    
    /**
     * @var Decklist[]
     */
    private $favorites;
    
    /**
     * @var Decklist[]
     */
    private $votes;
    
    /**
     * @var User[]
     */
    private $following;
    
    /**
     * @var User[]
     */
    private $followers;
    
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
     * Set reputation
     *
     * @param integer $reputation
     * @return User
     */
    public function setReputation($reputation)
    {
        $this->reputation = $reputation;
    
        return $this;
    }

    /**
     * Get reputation
     *
     * @return integer 
     */
    public function getReputation()
    {
        return $this->reputation;
    }

    /**
     * Set creation
     *
     * @param \DateTime $creation
     * @return User
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
     * Set resume
     *
     * @param string $resume
     * @return User
     */
    public function setResume($resume)
    {
        $this->resume = $resume;
    
        return $this;
    }

    /**
     * Get resume
     *
     * @return string 
     */
    public function getResume()
    {
        return $this->resume;
    }

    /**
     * Set role
     *
     * @param integer $role
     * @return User
     */
    public function setRole($role)
    {
        $this->role = $role;
    
        return $this;
    }

    /**
     * Get role
     *
     * @return integer 
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @return Role[]
     */
    public function getRoles()
    {
    	$roles = array('ROLE_USER');
    	if($this->getEmail() == "alsciende@gmail.com") $roles[] = 'ROLE_ADMIN';
    	return $roles;
    }
    
    /**
     * Set status
     *
     * @param integer $status
     * @return User
     */
    public function setStatus($status)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return integer 
     */
    public function getStatus()
    {
        return $this->status;
    }
    
    /**
     * Set faction
     *
     * @param string $faction
     * @return User
     */
    public function setFaction($faction)
    {
    	$this->faction = $faction;
    
    	return $this;
    }
    
    /**
     * Get faction
     *
     * @return string
     */
    public function getFaction()
    {
    	return $this->faction;
    }
    
    /**
     * Set avatar
     *
     * @param string $avatar
     * @return User
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;
    
        return $this;
    }

    /**
     * Get avatar
     *
     * @return string 
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * Set donation
     *
     * @param integer $donation
     * @return User
     */
    public function setDonation($donation)
    {
        $this->donation = $donation;
    
        return $this;
    }
    
    /**
     * Get donation
     *
     * @return integer
     */
    public function getDonation()
    {
        return $this->donation;
    }
    
    /**
     * Set deck
     *
     * @param string $decks
     * @return User
     */
    public function setDecks($decks)
    {
    	$this->decks = $decks;
    
    	return $this;
    }
    
    /**
     * Get deck
     *
     * @return \Netrunnerdb\BuilderBundle\Entity\Deck[]
     */
    public function getDecks()
    {
    	return $this->decks;
    }

    /**
     * Set decklists
     *
     * @param string $decklists
     * @return Deck
     */
    public function setDecklists($decklists)
    {
    	$this->decklists = $decklists;
    
    	return $this;
    }
    
    /**
     * Get decklists
     *
     * @return string
     */
    public function getDecklists()
    {
    	return $this->decklists;
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
     * Set opinions
     *
     * @param string $opinions
     * @return Deck
     */
    public function setOpinions($opinions)
    {
    	$this->opinions = $opinions;
    
    	return $this;
    }
    
    /**
     * Get opinions
     *
     * @return string
     */
    public function getOpinions()
    {
    	return $this->opinions;
    }
    
    /**
     * Add to favorites
     *
     * @param Decklist $favorites
     * @return User
     */
    public function addFavorite($decklist)
    {
    	$decklist->addFavorite($this);
    	$this->favorites[] = $decklist;
    
    	return $this;
    }

    /**
     * Remove from favorites
     *
     * @param Decklist $favorites
     * @return User
     */
    public function removeFavorite($decklist)
    {
    	$decklist->removeFavorite($this);
    	$this->favorites->removeElement($decklist);
    	
    	return $this;
    }
    
    
    /**
     * Get favorites
     *
     * @return Decklist
     */
    public function getFavorites()
    {
    	return $this->favorites;
    }
    
    /**
     * Set votes
     *
     * @param Decklist $votes
     * @return User
     */
    public function addVote($decklist)
    {
    	$decklist->addVote($this);
    	$this->votes[] = $decklist;
    
    	return $this;
    }
    
    /**
     * Get votes
     *
     * @return Decklist
     */
    public function getVotes()
    {
    	return $this->votes;
    }

    /**
     * Set following
     *
     * @param User $following
     * @return User
     */
    public function addFollowing($user)
    {
    	$user->addFollower($this);
    	$this->following[] = $user;
    
    	return $this;
    }
    
    /**
     * Get following
     *
     * @return User
     */
    public function getFollowing()
    {
    	return $this->following;
    }

    /**
     * Add follower
     *
     * @param User $follower
     * @return User
     */
    public function addFollower($user)
    {
    	$this->followers[] = $user;
    
    	return $this;
    }
    
    /**
     * Get followers
     *
     * @return User
     */
    public function getFollowers()
    {
    	return $this->followers;
    }
    
    public function getMaxNbDecks()
    {
    	return 100+floor($this->reputation/ 10);
    }
    
    public function __construct()
    {
    	$this->decks = new ArrayCollection();
    	$this->decklists = new ArrayCollection();
       	$this->comments = new ArrayCollection();
       	$this->opinions = new ArrayCollection();
       	$this->favorites = new ArrayCollection();
       	$this->votes = new ArrayCollection();
       	$this->following = new ArrayCollection();
       	$this->followers = new ArrayCollection();
       	$this->reputation = 1;
       	$this->faction = 'neutral';
       	$this->creation = new \DateTime();
       	
       	parent::__construct();
    }
}
