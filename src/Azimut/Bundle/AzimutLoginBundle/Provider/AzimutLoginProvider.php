<?php
/**
 * Created by mikaelp on 12/15/2015 3:50 PM
 */

namespace Azimut\Bundle\AzimutLoginBundle\Provider;

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AzimutLoginProvider extends AbstractProvider
{
    const CACHE_FILE = 'azimutloginaccesstoken.serialized.php';

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var AccessToken|string|null
     */
    private $clientToken;

    /**
     * @var string
     */
    private $cacheFolder;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct($baseUrl, $clientId, $clientSecret, $cacheFolder, Filesystem $filesystem, TokenStorageInterface $tokenStorage)
    {
        $this->baseUrl = $baseUrl;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->cacheFolder = $cacheFolder;
        $this->filesystem = $filesystem;
        $this->tokenStorage = $tokenStorage;

        parent::__construct();
    }

    /**
     * Returns the base URL for authorizing a client.
     *
     * Eg. https://oauth.service.com/authorize
     *
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        // TODO: Implement getBaseAuthorizationUrl() method.
    }

    public function getBaseAccessTokenUrl(array $params)
    {
        return $this->baseUrl.'/oauth/v2/token';
    }

    /**
     * Returns the URL for requesting the resource owner's details.
     *
     * @param AccessToken $token
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        // TODO: Implement getResourceOwnerDetailsUrl() method.
    }

    /**
     * Returns the default scopes used by this provider.
     *
     * This should only be the scopes that are required to request the details
     * of the resource owner, rather than all the available scopes.
     *
     * @return array
     */
    protected function getDefaultScopes()
    {
        return ['USER'];
    }

    /**
     * Checks a provider response for errors.
     *
     * @throws IdentityProviderException
     * @param  ResponseInterface $response
     * @param  array|string      $data Parsed response data
     * @return void
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (array_key_exists('error', $data)) {
            throw new IdentityProviderException($data['error'].': '.$data['error_description'], 0, $response);
        }
    }

    /**
     * Generates a resource owner object from a successful resource owner
     * details request.
     *
     * @param  array       $response
     * @param  AccessToken $token
     * @return ResourceOwnerInterface
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        // TODO: Implement createResourceOwner() method.
    }

    protected function getAccessTokenMethod()
    {
        return self::METHOD_GET;
    }

    protected function getAuthorizationHeaders($token = null)
    {
        if ($token) {
            return [
                'Authorization' => 'Bearer '.$token,
            ];
        }

        return [];
    }

    public function getAuthenticatedRequest($method, $url, $token, array $options = [])
    {
        // Leading / in $url
        if (substr($url, 0, 1) == '/') {
            $url = substr($url, 1);
        }

        // Trailing / in $this->baseUrl
        if (substr($this->baseUrl, -1) == '/') {
            $url = $this->baseUrl.$url;
        } else {
            $url = $this->baseUrl.'/'.$url;
        }

        return parent::getAuthenticatedRequest($method, $url, $token, $options);
    }


    public function getToken($forceNew = false)
    {
        if (!$forceNew && $this->getTokenFromSession()) {
            return $this->getTokenFromSession();
        }

        if ($forceNew) {
            $needNewToken = true;
        } else {
            if (!$this->clientToken) {
                $needNewToken = true;
                if ($token = $this->getTokenFromCache()) {
                    $this->clientToken = $token;
                    $needNewToken = false;
                    if ($token->hasExpired()) {
                        $needNewToken = true;
                    }
                }
            } else {
                $needNewToken = false;
            }
        }

        if ($needNewToken) {
            $this->clientToken = $this->getAccessToken('client_credentials');
            $this->storeTokenInCache($this->clientToken);
        }

        return $this->clientToken;
    }

    /**
     * @return AccessToken|null
     */
    private function getTokenFromCache()
    {
        $cacheFile = $this->cacheFolder.DIRECTORY_SEPARATOR.self::CACHE_FILE;
        if ($this->filesystem->exists($cacheFile)) {
            return unserialize(file_get_contents($cacheFile));
        }

        return null;
    }

    private function storeTokenInCache(AccessToken $clientToken)
    {
        $cacheFile = $this->cacheFolder.DIRECTORY_SEPARATOR.self::CACHE_FILE;
        $this->filesystem->dumpFile($cacheFile, serialize($clientToken));
    }

    /**
     * @return null|string
     */
    private function getTokenFromSession()
    {
        if ($this->tokenStorage->getToken() && $this->tokenStorage->getToken() instanceof OAuthToken) {
            /** @var OAuthToken $token */
            $token = $this->tokenStorage->getToken();

            return $token->getAccessToken();
        }

        return null;
    }
}
