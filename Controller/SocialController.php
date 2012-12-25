<?php


namespace Ailove\AbstractSocialBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

abstract class SocialController extends Controller
{
    /**
     * Creating empty social network user token then redirecting to secure route
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function loginAction()
    {
        $this->get('security.context')->setToken($this->createAnonymousToken());
        $response = $this->redirect($this->generateUrl($this->getSecuredAreaRoute()));
        $response->headers->setCookie(new \Symfony\Component\HttpFoundation\Cookie('_social_entry_point_referer', $this->getRequest()->headers->get('referer')));
        return $response;
    }

    public function connectAction()
    {
        //this method should be secured to call an authentication entry point
        return new RedirectResponse('/');

    }

    /**
     * Implement this method to create empty token of your social network
     * @return \Symfony\Component\Security\Core\Authentication\Token\TokenInterface
     */
    abstract protected function createAnonymousToken();

    /**
     * Implement this to return a name of a secured route where we'll redirect after creating empty token to start authentication
     * via authenticaiton entry point. Secured route should point to actual controller action.
     * @return string
     */
    abstract protected function getSecuredAreaRoute();
}
