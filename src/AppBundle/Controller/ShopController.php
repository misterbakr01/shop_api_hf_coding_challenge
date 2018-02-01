<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use AppBundle\Document\Shop;
use AppBundle\Document\LikedShop;
use AppBundle\Document\DislikedShop;

class ShopController extends Controller
{
    /**
     * @Rest\View()
     * @Rest\Get("/api/shops/nearby")
     */
    public function shopsAction()
    {
        $liked_shops = $this->preferredAction();
        $disliked_shops = $this->dislikedAction();

        $shops = $this->get('doctrine_mongodb')
            ->getRepository('AppBundle:Shop')
            ->findAll();

        if (!$shops) {
            throw $this->createNotFoundException('No shop found');
        }

        return $shops;
    }

    /**
     * @Rest\View()
     * @Rest\Get("/api/shops/preferred")
     */
    public function preferredAction()
    {
        $liked_shops = $this->get('doctrine_mongodb')
            ->getRepository('AppBundle:LikedShop')
            ->findAll();

        if (!$liked_shops) {
            throw $this->createNotFoundException('No shop found');
        }

        return $liked_shops;
    }

    /**
     * @Rest\View()
     * @Rest\Post("/api/shop/like")
     */
    public function likeAction(Request $request)
    {

        $post_data = $request->request->all();
        if(!empty($post_data) && (!empty($post_data['user_id']) && !empty($post_data['shop_id']))){
          $liked = new LikedShop();
          $liked->setUserId($post_data['user_id']);

          $shop = $this->get('doctrine_mongodb')
              ->getRepository('AppBundle:Shop')
              ->findOneById($post_data['shop_id']);

          $liked->setShop($shop);
          $dm = $this->get('doctrine_mongodb')->getManager();
          $dm->persist($liked);
          $dm->flush();
          return $liked;
        }
        return \FOS\RestBundle\View\View::create(['message' => 'Invalid params'], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @Rest\View()
     * @Rest\Delete("/api/shop/unlike/{id}")
     */
    public function unlikeAction(Request $request)
    {
        if($request->get('id')){
          $liked = $this->get('doctrine_mongodb')
              ->getRepository('AppBundle:LikedShop')
              ->findOneById($request->get('id'));

          $dm = $this->get('doctrine_mongodb')->getManager();
          $dm->remove($liked);
          $dm->flush();
        }
        return \FOS\RestBundle\View\View::create(['message' => 'Invalid params'], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @Rest\View()
     * @Rest\Post("/api/shop/dislike")
     */
    public function dislikeAction(Request $request)
    {
        $post_data = $request->request->all();
        if(!empty($post_data) && (!empty($post_data['user_id']) && !empty($post_data['shop_id']))){
          $disliked = new DislikedShop();
          $disliked->setUserId($post_data['user_id']);
          $disliked->setShopId($post_data['shop_id']);
          $disliked->setCreatedAt(new \DateTime());
          $dm = $this->get('doctrine_mongodb')->getManager();
          $dm->persist($disliked);
          $dm->flush();
          return $disliked;
        }
        return \FOS\RestBundle\View\View::create(['message' => 'Invalid params'], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @Rest\View()
     * @Rest\Get("/api/shop/disliked")
     */
    public function dislikedAction()
    {
        $liked_shops = $this->get('doctrine_mongodb')
            ->getRepository('AppBundle:DislikedShop')
            ->findAll();

        if (!$liked_shops) {
            throw $this->createNotFoundException('No DislikedShop found');
        }

        return $liked_shops;
    }

}
