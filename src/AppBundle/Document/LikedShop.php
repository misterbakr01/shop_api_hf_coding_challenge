<?php
namespace AppBundle\Document;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(collection="likes")
 */
class LikedShop
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\Field(type="string",name="user_id")
     */
    public $userId;

    /**
     * @MongoDB\ReferenceOne(targetDocument="Shop")
     */
    public $shop;



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
     * Set userId
     *
     * @param string $userId
     * @return $this
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * Get userId
     *
     * @return string $userId
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set shop
     *
     * @param AppBundle\Document\Shop $shop
     * @return $this
     */
    public function setShop(\AppBundle\Document\Shop $shop)
    {
        $this->shop = $shop;
        return $this;
    }

    /**
     * Get shop
     *
     * @return AppBundle\Document\Shop $shop
     */
    public function getShop()
    {
        return $this->shop;
    }
}
