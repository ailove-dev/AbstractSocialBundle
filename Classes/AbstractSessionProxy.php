<?php


namespace Ailove\AbstractSocialBundle\Classes;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

abstract class AbstractSessionProxy
{

    /**
     * @var string
     */
    protected $dialogUrl;

    /**
     * @var string
     */
    protected $redirectUri;

    /**
     * @var string
     */
    protected $redirectRoute;

    /**
     * @var string
     */
    protected $scope;

    /**
     * @var string
     */
    protected $responseType;

    /**
     * @var string
     */
    protected $accessTokenUrl;

    /**
     * @var string
     */
    protected $accessParams;

    /**
     * @var string
     */
    protected $authJson;

    /**
     * @var SdkInterface
     */
    protected $sdk;

    /**
     * @var \Symfony\Component\DependencyInjection\Container
     */
    protected $serviceContainer;

    /**
     * @var Request
     */
    protected $request;

    /**
     * Constructor.
     *
     * @param array $params params of this proxy
     *
     * @throws \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function __construct(
        $params = array()
    )
    {

        foreach ($this->getRequiredParams() as $param) {
            if (empty($params[$param])) {
                throw new \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException('required param ' . $param . ' is missing');
            } else {
                $this->$param = $params[$param];
            }
        }


    }

    public function getRequiredParams()
    {
        return array(
            'accessTokenUrl',
            'dialogUrl',
            'responseType',
            'redirectRoute',
            'scope',
        );
    }

    /**
     * Container setter injection (because interface blocks constructor injection)
     *
     * @param \Symfony\Component\DependencyInjection\Container $container
     */
    public function setContainer(Container $container)
    {
        $this->serviceContainer = $container;
    }

    /**
     * Container setter injection (because interface blocks constructor injection)
     * @param SdkInterface $sdk
     */
    public function setSdk($sdk)
    {
        $this->sdk = $sdk;
    }

    /**
     * Container setter injection (because interface blocks constructor injection)
     *
     * @return SdkInterface
     */
    public function getSdk()
    {
//        if (!$this->sdk->getAccessToken()) {
//            var_dump($this->getAccessToken());die;
            $this->sdk->setAccessToken($this->getAccessToken());
//        }

        return $this->sdk;
    }

    /**
     * Stores the given ($key, $value) pair, so that future calls to getPersistentData($key) return $value. This call may be in another request.
     *
     * @param string $key   Key for persisting value
     * @param string $value Value to persist
     *
     * @return void
     */
    protected function setPersistentData($key, $value)
    {
        $this->getSession()->set($this->constructSessionVariableName($key), $value);
    }

    /**
     * Get the data for $key
     *
     * @param string  $key     The key of the data to retrieve
     * @param boolean $default The default value to return if $key is not found
     *
     * @return mixed
     */
    protected function getPersistentData($key, $default = false)
    {
        $sessionVariableName = $this->constructSessionVariableName($key);
        if ($this->getSession()->has($sessionVariableName)) {
            return $this->getSession()->get($sessionVariableName);
        }

        return $default;
    }

    /**
     * Construct session variable name.
     *
     * @param string $key Key name
     *
     * @return string
     */
    protected function constructSessionVariableName($key)
    {
        return $this->getSessionPrefix() . implode('_', array($this->sdk->getAppId(), $key));
    }

    /**
     * prefix to store session vars
     *
     * @return string
     */
    abstract protected function getSessionPrefix();

    /**
     * Generates redirect URI based on redirect_route param
     * @return string
     */
    protected function generateRedirectUri()
    {
        if (is_null($this->redirectUri)) {
            $this->redirectUri = $this->serviceContainer->get('router')->generate($this->getRedirectRoute(), array(), true);
        }
        return $this->redirectUri;
    }

    /**
     * variable name of the $_GET request that passed in by foreign resource
     * @return string
     */
    protected function getCodeVarName()
    {
        return 'code';
    }

    /**
     * URI to get authorization code
     * @return string
     */
    public function getAuthUri()
    {
        return $this->dialogUrl . '?client_id=' . $this->getSdk()->getAppId() .
                    '&redirect_uri=' . urlencode($this->generateRedirectUri()).
                    '&scope=' . $this->scope.
                    '&response_type=' . $this->responseType.
                    '&state=' . $this->getPersistentData('state');
    }

    /**
     * URI to get access token with received code
     * @param string $code
     * @return string
     */
    protected function getAccessTokenUri($code)
    {
        return $this->accessTokenUrl .
                        '?client_id=' . $this->getSdk()->getAppId().
                        '&client_secret=' . $this->getSdk()->getAppSecret().
                        '&grant_type=authorization_code' .
                        '&code=' . $code .
                        '&redirect_uri=' . urlencode($this->generateRedirectUri());
    }

    /**
     * @param string $url
     * @return string
     */
    protected function makeAuthorizationRequest($url)
    {
        try {
            return file_get_contents($url);
        } catch (\Exception $e) {
            return '';
        }

    }

    /**
     * Authorize client.
     *
     * @param string $redirectUri Redirect URI
     *
     * @return bool|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function authorize($redirectUri = null)
    {
        $result = false;

        if (null !== $redirectUri) {
            $this->redirectUri = $redirectUri;
        }

        $this->authJson = (array)json_decode($this->getPersistentData('authJson'));

        if ($this->authJson && isset($this->authJson['access_token'])) {

            // Data already stored in the session
            $result = true;
            $this->authJson =  $this->getPersistentData('authJson');

        } else {

            $code = $this->getRequest()->get($this->getCodeVarName());

            if (empty($code)) {
                // Redirect to auth
                $this->setPersistentData('state', md5(uniqid(rand(), true))); // CSRF protection

//                return new RedirectResponse($this->getAuthUri());
                return false;

            } else {

                $this->authJson = $this->makeAuthorizationRequest($this->getAccessTokenUri($code));


                if ($this->getAccessToken()) {
                    $this->setPersistentData('authJson', $this->authJson);
                    $this->setPersistentData('uid', $this->getUserId());

                    $result = true;
                } else {
                    $result = false;
                }

            }
        }

        return $result;
    }

    public function getRedirectRoute()
    {
        return $this->redirectRoute;
    }

    /**
     * Make this method to use authorized sdk in order to fetch user's id from social network
     * @return string User's social uid
     */
    abstract function fetchUserId();

    /**
     * Get user id.
     *
     * @return string
     */
    public function getUserId()
    {
        if (false === $this->getPersistentData('uid') && null === $this->sdk) {
            return false;
        }

        if (false === $this->getPersistentData('uid') && null !== $this->sdk) {
            $this->sdk->setAccessToken($this->getAccessToken());

            try {
//                $user = $this->sdk->api('users.getCurrentUser');
//
//                return $user['uid'];
                $user = $this->fetchUserId();

                return $user;
            } catch (\Exception $e) {
                return false;
            }
        }

        return $this->getPersistentData('uid');
    }

    /**
     * Get access token.
     *
     * @return string
     */
    public function getAccessToken()
    {

        $accessParams = $this->getAccessParams();

        return isset($accessParams['access_token']) ? $accessParams['access_token'] : '';
    }

    /**
     * @return string
     */
    public function getAccessParams()
    {
        if (null === $this->accessParams) {
            $this->accessParams = json_decode((string)$this->getAuthJson(), true);
            //probably data isn't in json format, like on facebook for example, let's try to parse a string
            if (!$this->accessParams && !is_array($this->authJson)) {
                $data = array();
                parse_str($this->authJson, $data);

                if (!empty($data['access_token'])) {
                    $this->accessParams = $data;
                }
            }
        }

        return $this->accessParams;
    }

    /**
     * Get expires time.
     *
     * @return string
     */
    public function getExpiresIn()
    {
        if (null === $this->accessParams) {
            $this->accessParams = json_decode($this->getAuthJson(), true);
        }

        return $this->accessParams['expires_in'];
    }

    /**
     * Get authorization JSON string.
     *
     * @return string
     */
    protected function getAuthJson()
    {
        if (null === $this->authJson) {
            $this->authJson = $this->getPersistentData('authJson');
        }

        return $this->authJson;
    }

    /**
     * Return request
     *
     * @return Request
     */
    public function getRequest()
    {
        if (empty($this->request)) {
            $this->request = $this->serviceContainer->get('request');
        }

        return $this->request;
    }

    /**
     * Return session
     *
     * @return Session
     */
    public function getSession()
    {
        return $this->getRequest()->getSession();
    }

}


