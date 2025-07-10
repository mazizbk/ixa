<?php
/**
 * Created by mikaelp on 12/15/2015 3:54 PM
 */

namespace Azimut\Bundle\AzimutLoginBundle\Service;

use Azimut\Bundle\AzimutLoginBundle\Model\User;
use Azimut\Bundle\AzimutLoginBundle\Provider\AzimutLoginProvider;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use League\OAuth2\Client\Provider\AbstractProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AzimutLoginClient
{
    /**
     * @var AbstractProvider
     */
    private $provider;

    /**
     * @var Client
     */
    private $guzzleClient;

    public function __construct(AzimutLoginProvider $provider)
    {
        $this->provider = $provider;
        $this->guzzleClient = new Client([
            'http_errors' => false,
        ]);
    }

    private function getContent($method, $url, $params = [], $token = null, array $headers = [])
    {
        $result = $this->makeCall($method, $url, $params, $token, $headers);

        return $result->getBody()->getContents();
    }

    private function makeCall($method, $url, $params = [], $token = null, array $headers = [])
    {
        $hadToken = !is_null($token);
        if (!$hadToken) {
            $token = $this->provider->getToken();
        }

        $requestOptions = [];
        if (is_array($params) && count($params) > 0) {
            if ($method == Request::METHOD_POST || $method == Request::METHOD_PUT) {
                $requestOptions['form_params'] = $params;
            } else {
                $requestOptions['query'] = $params;
            }
        }

        $request = $this->provider->getAuthenticatedRequest($method, $url, $token, ['headers' => $headers]);
        try {
            $result = $this->guzzleClient->send($request, $requestOptions);
            if ($result->getStatusCode() >= 400) {
                throw BadResponseException::create($request, $result);
            }
        } catch (ClientException $exception) {
            // If we had a token, it was user token, we don't need to generate an application token
            if (!$hadToken && $exception->getResponse()->getStatusCode() == Response::HTTP_UNAUTHORIZED) {
                // Force new token
                $token = $this->provider->getToken(true);
                $request = $this->provider->getAuthenticatedRequest($method, $url, $token, ['headers' => $headers]);
                $result = $this->guzzleClient->send($request, $requestOptions);
            } else {
                throw $exception;
            }
        }

        return $result;
    }

    /**
     * Get information about current user (using access token)
     * @param string $token The access token
     * @return User
     */
    public function getMe($token)
    {
        $response = $this->getContent(Request::METHOD_GET, '/api/oauthserver/v2/users/me', [], $token);
        $user = self::decodeJson($response);

        if (!array_key_exists('user', $user)) {
            throw new \InvalidArgumentException($response);
        }
        $user = $user['user'];

        return User::fromAPIResponse($user);
    }

    /**
     * List all users
     * @return User[]
     */
    public function getUsers()
    {
        $response= $this->getContent('GET', '/api/oauthserver/v2/users');
        $users = self::decodeJson($response);
        if (!array_key_exists('users', $users)) {
            throw new \InvalidArgumentException($response);
        }
        $users = $users['users'];

        $finalUsers = [];
        foreach ($users as $user) {
            $finalUsers[] = User::fromAPIResponse($user);
        }

        return $finalUsers;
    }

    /**
     * Get information about a user
     * @param int $id The user ID
     * @return User
     */
    public function getUser($id)
    {
        if (!is_int($id)) {
            throw new \InvalidArgumentException('Parameter $id must be of type integer, '.gettype($id).' given');
        }
        $response = $this->getContent(Request::METHOD_GET, '/api/oauthserver/v2/users/'.$id);
        $user = self::decodeJson($response);

        if (!array_key_exists('user', $user)) {
            throw new \InvalidArgumentException($response);
        }
        $user = $user['user'];

        return User::fromAPIResponse($user);
    }

    /**
     * Search for a user by username, email, firstName or lastName
     * @param array $criteria
     * @return User[]
     */
    public function searchUser($criteria)
    {
        $validParameters = ['username', 'email', 'firstName', 'lastName'];

        $rqCriteria = [];
        foreach ($validParameters as $parameter) {
            if (array_key_exists($parameter, $criteria)) {
                $rqCriteria[$parameter] = $criteria[$parameter];
            }
        }

        $response = $this->getContent(Request::METHOD_GET, '/api/oauthserver/v2/users/search', $rqCriteria);
        $users = self::decodeJson($response);
        if (!array_key_exists('users', $users)) {
            throw new \InvalidArgumentException($response);
        }
        $users = $users['users'];

        $finalUsers = [];
        foreach ($users as $user) {
            $finalUsers[] = User::fromAPIResponse($user);
        }

        return $finalUsers;
    }

    /**
     * Fetch a user by its email address
     * Shortcut for searchUser(['email' => $email])
     * @param string $email The email address
     * @return User|bool The user, or false
     */
    public function getUserByEmailAddress($email)
    {
        if (false===filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('$email must be a valid email address');
        }

        $users = $this->searchUser(['email' => $email]);

        if (count($users) == 1) {
            return $users[0];
        }

        return false;
    }

    /**
     * Save a new user
     *
     * @param User $user The user to be created
     * @param string $locale
     * @param null $token Access token to be used
     * @param bool $grantAccessToCurrentApp Whether to grant user access to current application
     * @return User|bool The created user
     */
    public function createUser(User $user, $locale, $token = null, $grantAccessToCurrentApp = true)
    {
        $response = $this->getContent(Request::METHOD_POST, '/api/oauthserver/v2/users', [
            'user[email]' => $user->getEmail(),
            'user[hasAccessToCurrentApp]' => $grantAccessToCurrentApp?'true':'false'
        ], $token, [
            'Accept-Language' => $locale,
        ]);

        $apiUser = self::decodeJson($response);
        if (!array_key_exists('user', $apiUser)) {
            throw new \InvalidArgumentException($response);
        }

        return User::fromAPIResponse($apiUser['user']);
    }

    public function createUserEmail($email, $locale, $token = null, $grantAccessToCurrentApp = true)
    {
        if (false === filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('$email must be a valid email address');
        }

        $user = new User();
        $user->setEmail($email);

        return $this->createUser($user, $locale, $token, $grantAccessToCurrentApp);
    }

    public function authorizeUser(User $user, $token = null)
    {
        $response = $this->makeCall(Request::METHOD_POST, '/api/oauthserver/v2/users/'.$user->getId().'/authorize', $token);

        return $response->getStatusCode() == Response::HTTP_OK || $response->getStatusCode() == Response::HTTP_CREATED;
    }

    public function unauthorizeUser(User $user, $token = null)
    {
        $response = $this->makeCall(Request::METHOD_DELETE, '/api/oauthserver/v2/users/'.$user->getId().'/authorize', $token);

        return $response->getStatusCode() == Response::HTTP_OK;
    }

    public function reSendValidationEmail(User $user, $token = null)
    {
        $response = $this->makeCall(Request::METHOD_POST, '/api/oauthserver/v2/users/'.$user->getId().'/validationemail', $token);

        return $response->getStatusCode() == Response::HTTP_OK;
    }

    protected static function decodeJson($input)
    {
        $decoded = json_decode($input, true);
        if (json_last_error() != JSON_ERROR_NONE) {
            throw new \RuntimeException(sprintf(
                'Unable to decode JSON input. Error is %d %s\nInput was:\n%s',
                json_last_error(),
                json_last_error_msg(),
                $input
            ));
        }

        return $decoded;
    }
}
