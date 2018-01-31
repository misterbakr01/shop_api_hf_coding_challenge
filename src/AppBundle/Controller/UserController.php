<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\View\ViewHandler;
use FOS\RestBundle\View\View;
use AppBundle\Document\User;
use AppBundle\Entity\AuthToken;
use AppBundle\Document\Shop;

class UserController extends Controller
{

    /**
     * @Rest\View(statusCode=Response::HTTP_CREATED, serializerGroups={"token"})
     * @Rest\Get("/api/signup")
     */
    public function signupAction(Request $request)
    {

      $user = new User();

      $data = $request->request->all();
      if (($request->request->method == "POST") && !empty($data)) {

          $user->setEmail($data->email);
          $encoder = $this->get('security.password_encoder');
          // le mot de passe en claire est encodé avant la sauvegarde
          $encoded = $encoder->encodePassword($user, $data->password);
          $user->setPassword($encoded);

          $em = $this->get('doctrine_mongodb');
          $em->persist($user);
          $em->flush();

          //Generate Token
          $authToken = new AuthToken();
          $authToken->setValue(base64_encode(random_bytes(50)));
          $authToken->setCreatedAt(new \DateTime('now'));
          $authToken->setUser($user);

          $em->persist($authToken);
          $em->flush();

          return $authToken;
      } else {
          return array('error' => 'errororr' );
      }


      $dm = $this->get('doctrine_mongodb')->getManager();
      $dm->persist($shop);
      $dm->flush();

        $apiKey = $request->query->get('apikey');
        $credentials = new Credentials();
        $form = $this->createForm(CredentialsType::class, $credentials);

        $form->submit($request->request->all());

        if (!$form->isValid()) {
            return $form;
        }

        $em = $this->get('doctrine_mongodb');

        $user = $em->getRepository('AppBundle:User')
            ->findOneByEmail($credentials->getLogin());

        if (!$user) { // L'utilisateur n'existe pas
            return $this->invalidCredentials();
        }

        $encoder = $this->get('security.password_encoder');
        $isPasswordValid = $encoder->isPasswordValid($user, $credentials->getPassword());

        if (!$isPasswordValid) { // Le mot de passe n'est pas correct
            return $this->invalidCredentials();
        }

        $authToken = new AuthToken();
        $authToken->setValue(base64_encode(random_bytes(50)));
        $authToken->setCreatedAt(new \DateTime('now'));
        $authToken->setUser($user);

        $em->persist($authToken);
        $em->flush();

        return $authToken;
    }

    /**
     * @Rest\Get("/api/signin")
     */
    public function signinAction()
    {
        $shops = $this->get('doctrine_mongodb')
            ->getRepository('AppBundle:Shop')
            ->findAll();

        if (!$shops) {
            throw $this->createNotFoundException('No shop found for id ');
        }
       $viewHandler = $this->get('fos_rest.view_handler');

       $view = View::create($shops);
       $view->setFormat('json');

       return $viewHandler->handle($view);
    }

    private function invalidCredentials()
    {
        return \FOS\RestBundle\View\View::create(['message' => 'Invalid credentials'], Response::HTTP_BAD_REQUEST);
    }
    /**
     * @Rest\Get("/api/create")
     */
    public function createAction()
    {

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
     * @Rest\Get("/api/show")
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

}
