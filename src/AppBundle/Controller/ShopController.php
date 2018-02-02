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
//use AppBundle\Repository\ShopRepository;
use AppBundle\Document\LikedShop;
use AppBundle\Document\DislikedShop;

class ShopController extends Controller
{

    /**
     * @Rest\View()
     * @Rest\Get("/api/shops/nearby")
     */
    public function shopsAction(Request $request)
    {
        $connectedUser = $this->get('security.token_storage')->getToken()->getUser();
        $page = $request->get('page');
        $page = $page - 1;
        $skip = $page * 20;
        $liked_ids = $this->getLikedShops($connectedUser);
        $disliked_shops = $this->getDisLikedShops($connectedUser);
        $exclude_ids = array_merge($liked_ids, $disliked_shops);

        $shops = $this->get('doctrine_mongodb')
            ->getRepository('AppBundle:Shop')
            ->findAllExclude($exclude_ids,20,$skip);

        $user_coord = ['longitude'=>$request->get('longitude'),'latitude'=>$request->get('latitude')];

        $result = [];
        foreach ($shops as $key => $shop) {
          $shop_location = $shop->getLocation();
          $shop_coord = ['longitude'=>$shop_location['coordinates'][0],'latitude'=> $shop_location['coordinates'][1]];
          $distanceBetwineUserAndShop = $this->distance($shop_coord,$user_coord);
          $shop->setDistance($distanceBetwineUserAndShop);
          $result[] = $shop;
        }
        usort($result, array($this,"comparDistance"));
        if (!$shops) {
            $response = new JsonResponse(array('success'=>false,'errors' => ["No shop found"]),Response::HTTP_NOT_FOUND);
        }

        $response = new JsonResponse(array('success'=>true,'data' => $this->serialize($result)));
        return $response;
    }

    private function getLikedShops($connectedUser)
    {
      $liked_shops = $this->get('doctrine_mongodb')
          ->getRepository('AppBundle:LikedShop')
          ->findByUserId($connectedUser->getId());

      $liked_ids = [];
      foreach ($liked_shops as $key => $value) {
        $liked_ids[]=$value->getId();
      }
      return $liked_ids;
    }

    private function getDisLikedShops($connectedUser)
    {
      $disliked_shops = $this->get('doctrine_mongodb')
          ->getRepository('AppBundle:DislikedShop')
          ->findByUserId($connectedUser->getId());

      $disliked_ids = [];
      foreach ($disliked_shops as $key => $value) {
        $liked_ids[]=$value->getId();
      }
      return $disliked_ids;
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
            $response = new JsonResponse(array('success'=>false,'errors' => ["No shop found"]),Response::HTTP_NOT_FOUND);
        }
        $response = new JsonResponse(array('success'=>true,'data' => $this->serialize($liked_shops)));
        return $response;

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

          $response = new JsonResponse(array('success'=>true,'data' => $this->serialize($liked)));
          return $response;
        }else{
          $response = new JsonResponse(array('success'=>false,'errors' => ['Invalid params']),Response::HTTP_BAD_REQUEST);
        }

        return $response;
    }

    /**
     * @Rest\View()
     * @Rest\Delete("/api/shop/unlike/{id}")
     */
    public function unlikeAction(Request $request)
    {
        if($request->get('id')){
          $unliked = $this->get('doctrine_mongodb')
              ->getRepository('AppBundle:LikedShop')
              ->findOneById($request->get('id'));

          $dm = $this->get('doctrine_mongodb')->getManager();
          $dm->remove($unliked);
          $dm->flush();
          $response = new JsonResponse(array('success'=>true,'data' => $this->serialize($unliked)));
          return $response;
        }else{
          $response = new JsonResponse(array('success'=>false,'errors' => ['Invalid params']),Response::HTTP_BAD_REQUEST);
        }
        return $response;
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

          $response = new JsonResponse(array('success'=>true,'data' => $this->serialize($disliked)));
        }else{
          $response = new JsonResponse(array('success'=>false,'errors' => ['Invalid params']),Response::HTTP_BAD_REQUEST);
        }
        return $response;
    }

    /**
     * @Rest\View()
     * @Rest\Get("/api/shop/disliked")
     */
    public function dislikedAction()
    {
        $disliked_shops = $this->get('doctrine_mongodb')
            ->getRepository('AppBundle:DislikedShop')
            ->findAll();

        if (!$disliked_shops) {
          $response = new JsonResponse(array('success'=>'false','errors' => ['No Disliked Shop found']),Response::HTTP_NOT_FOUND);
        }else{
          $response = new JsonResponse(array('success'=>false,'data' => $this->serialize($disliked_shops) ));
        }
        return $response;
    }
    private function serialize($data)
    {
        return $this->container->get('jms_serializer')
            ->toArray($data);
    }

    private function distance($p1, $p2, $unit = "K") {

      $theta = $p1['longitude'] - $p2['longitude'];
      $dist = sin(deg2rad($p1['latitude'])) * sin(deg2rad($p2['latitude'])) +  cos(deg2rad($p1['latitude'])) * cos(deg2rad($p2['latitude'])) * cos(deg2rad($theta));
      $dist = acos($dist);
      $dist = rad2deg($dist);
      $miles = $dist * 60 * 1.1515;
      $unit = strtoupper($unit);

      if ($unit == "K") {
        return ($miles * 1.609344);
      } else if ($unit == "N") {
        return ($miles * 0.8684);
      } else {
          return $miles;
      }
    }

    private function comparDistance($d1,$d2)
    {
      return ($d1->getDistance() < $d2->getDistance()) ? -1 : 1;
    }
}
