<?php
namespace AppBundle\Document;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(collection="shops")
 */
class Shop
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\Field(type="string")
     */
    public $email;
    /**
     * @MongoDB\Field(type="string")
     */
    public $name;

    /**
     * @MongoDB\Field(type="string")
     */
    public $picture;
    /**
     * @MongoDB\Field(type="string")
     */
    public $city;
    /**
     * @MongoDB\Field(type="hash")
     */
    public $location = array();
     //
    //  /**
    //   * @MongoDB\ReferenceMany(targetDocument="LikedShop", mappedBy="shop")
    //   */
    //  public $liked;

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Get email
     *
     * @return string $email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set picture
     *
     * @param string $picture
     * @return $this
     */
    public function setPicture($picture)
    {
        $this->picture = $picture;
        return $this;
    }

    /**
     * Get picture
     *
     * @return string $picture
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return $this
     */
    public function setCity($city)
    {
        $this->city = $city;
        return $this;
    }

    /**
     * Get city
     *
     * @return string $city
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set location
     *
     * @param hash $location
     * @return $this
     */
    public function setLocation($location)
    {
        $this->location = $location;
        return $this;
    }

    /**
     * Get location
     *
     * @return hash $location
     */
    public function getLocation()
    {
        return $this->location;
    }
    // public function __construct()
    // {
    //     //$this->liked = new \Doctrine\Common\Collections\ArrayCollection();
    // }

    // /**
    //  * Add liked
    //  *
    //  * @param AppBundle\Document\LikedShop $liked
    //  */
    // public function addLiked(\AppBundle\Document\LikedShop $liked)
    // {
    //     $this->liked[] = $liked;
    // }
    //
    // /**
    //  * Remove liked
    //  *
    //  * @param AppBundle\Document\LikedShop $liked
    //  */
    // public function removeLiked(\AppBundle\Document\LikedShop $liked)
    // {
    //     $this->liked->removeElement($liked);
    // }
    //
    // /**
    //  * Get liked
    //  *
    //  * @return \Doctrine\Common\Collections\Collection $liked
    //  */
    // public function getLiked()
    // {
    //     return $this->liked;
    // }
}
