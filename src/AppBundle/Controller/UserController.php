<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use AppBundle\Form\Type\UserType;
use AppBundle\Form\Type\CredentialsType;
use AppBundle\Entity\Credentials;
use AppBundle\Document\User;
use AppBundle\Document\AuthToken;

class UserController extends Controller
{
    /**
     * @Rest\View(statusCode=Response::HTTP_CREATED, serializerGroups={"user-token"})
     * @Rest\Post("/api/signup")
     */
    public function signupAction(Request $request)
    {

      $user = new User();
      $form = $this->createForm(UserType::class, $user);

      $form->submit($request->request->all());
        if ($form->isValid()) {
          $user->setEmail($user->getEmail());
          $encoder = $this->get('security.password_encoder');
          $encoded = $encoder->encodePassword($user, $user->getPassword());
          $user->setPassword($encoded);

          $dm = $this->get('doctrine_mongodb')->getManager();
          $dm->persist($user);
          $dm->flush();

          $authToken = new AuthToken();
          $authToken->setValue(base64_encode(random_bytes(50)));
          $authToken->setCreatedAt(new \DateTime('now'));
          $authToken->setUserId($user->getId());

          $dm->persist($authToken);
          $dm->flush();
          //$json = $this->serialize($authToken);
          $response = new JsonResponse(array('success'=>true,'data' => $this->serialize($authToken)));
      } else {
          $response = new JsonResponse(array('success'=>false,'data' => $this->serialize($form)),Response::HTTP_BAD_REQUEST);
      }

      return $response;

    }

    /**
     * @Rest\View(statusCode=Response::HTTP_CREATED, serializerGroups={"user-token"})
     * @Rest\Post("/api/signin")
     */
    public function signinAction(Request $request)
    {
      $credentials = new Credentials();
        $form = $this->createForm(CredentialsType::class, $credentials);

      $form->submit($request->request->all());
      if ($form->isValid()) {

          $user = $this->get('doctrine_mongodb')
              ->getRepository('AppBundle:User')
              ->findOneByEmail($credentials->getEmail());

          if (!$user) {
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
          $authToken->setUserId($user->getId());
          $dm = $this->get('doctrine_mongodb')->getManager();
          $dm->persist($authToken);
          $dm->flush();
          $response = new JsonResponse(array('success'=> true,'data' => $this->serialize($authToken)));
      } else {
          $response = new JsonResponse(array('success'=> false,'data' => $this->serialize($form)));
      }

      //Response::HTTP_BAD_REQUEST
      return $response;
    }

    /**
     * @Rest\View(statusCode=Response::HTTP_NO_CONTENT)
     * @Rest\Delete("/api/signout")
     */
    public function signoutAction(Request $request)
    {
      $dm = $this->get('doctrine_mongodb')->getManager();
      $authToken = $dm->getRepository('AppBundle:AuthToken')
                  ->findOneByValue($request->headers->get('X-Auth-Token'));
      /* @var $authToken AuthToken */

      $connectedUser = $this->get('security.token_storage')->getToken()->getUser();
      // dump($authToken);
      // dump($connectedUser);die;
      if ($authToken && $authToken->user->getId() === $connectedUser->getId()) {
          $dm->remove($authToken);
          $dm->flush();
          return new JsonResponse(array('success'=>true,'message' => "successfuly signout"));
      } else {
          throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException();
      }
    }

    private function invalidCredentials()
    {
        return new JsonResponse(array('success'=>false,'errors' => ["Invalide Credentials"]),Response::HTTP_BAD_REQUEST);
    }

    private function serialize($data)
    {
        return $this->container->get('jms_serializer')
            ->toArray($data);
    }
}
