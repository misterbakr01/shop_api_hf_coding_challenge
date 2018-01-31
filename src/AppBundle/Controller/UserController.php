<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\View\ViewHandler;
use FOS\RestBundle\View\View;
use AppBundle\Document\User;
use AppBundle\Document\Shop;

class UserController extends Controller
{
    /**
     * @Get("/api/users")
     */
    public function getPlacesAction(Request $request)
    {
        return new JsonResponse([
            new User("email1@mail.fr", "dfgdgdgsdfsfsdfsff"),
            new User("email2@mail.fr", "sfsfsfsfsfsf"),
            new User("email3@mail.fr", "sdffffffffffffffffffdfsffs"),
        ]);
    }

    /**
     * @Get("/api/create")
     */
    public function createAction()
    {
      // $user = new User();
      // $user->setEmail('email2@mail.fr');
      // $user->setPassword('sdfffffffDDDfffffffffffdfsffs');
      //
      // $dm = $this->get('doctrine_mongodb')->getManager();
      // $dm->persist($user);
      // $dm->flush();

      $shop = new Shop();
      $shop->setEmail('email33@mail.fr');
      $shop->setPicture('http://placehold.it/150x150');
      $shop->setCity('RABAT');
      $shop->setLocation(array('type' => "point" , 'coordinates'=>[-6.74693, 33.83824]));


      $dm = $this->get('doctrine_mongodb')->getManager();
      $dm->persist($shop);
      $dm->flush();

      return new Response('Created shop id '.$shop->getId());
    }

    /**
     * @Get("/api/show")
     */
    public function showAction()
    {
        $shops = $this->get('doctrine_mongodb')
            ->getRepository('AppBundle:Shop')
            ->findAll();

        if (!$shops) {
            throw $this->createNotFoundException('No shop found for id '.$id);
        }

        return new JsonResponse($shops);
    }

    /**
     * @Get("/api/signup")
     */
    public function signupAction()
    {
        $shops = $this->get('doctrine_mongodb')
            ->getRepository('AppBundle:Shop')
            ->findAll();

        if (!$shops) {
            throw $this->createNotFoundException('No shop found for id '.$id);
        }

        return new JsonResponse($shops);
    }

    /**
     * @Get("/api/signin")
     */
    public function signinAction()
    {
        $shops = $this->get('doctrine_mongodb')
            ->getRepository('AppBundle:Shop')
            ->findAll();

        if (!$shops) {
            throw $this->createNotFoundException('No shop found for id '.$id);
        }
       $viewHandler = $this->get('fos_rest.view_handler');

       $view = View::create($shops);
       $view->setFormat('json');

       return $viewHandler->handle($view);
    }
}
