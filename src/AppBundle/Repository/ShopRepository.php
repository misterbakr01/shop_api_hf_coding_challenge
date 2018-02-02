<?php
namespace AppBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;

class ShopRepository extends DocumentRepository
{
    /**
     * @return Shop[]
     */
    public function findAllExclude($ids,$limit,$skip)
    {
    //dm = $this->get('doctrine_mongodb')->getManager();
      return $this->createQueryBuilder('AppBundle:Shop')->
               field('id')->notIn($ids)
               ->limit($limit)
               ->skip($skip)
               ->getQuery()->execute();
    }
}
