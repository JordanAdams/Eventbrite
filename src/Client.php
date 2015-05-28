<?php

namespace JordanAdams\Eventbrite;

use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\ResponseInterface;

class Client
{
    const API_URL = 'https://www.eventbriteapi.com/';

    /**
     * @var string
     */
    protected $token;

    /**
     * @var GuzzleClient
     */
    protected $client;

    /**
     * Create a new client instance
     *
     * @param string $token
     * @param GuzzleClient $client
     */
    public function __construct($token, GuzzleClient $client)
    {
        if (empty($token)) {
            throw new \InvalidArgumentException("OAuth token must be supplied");
        }

        $this->token = $token;
        $this->client = $client;
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function eventsSearch(array $params = [])
    {
        return $this->get('/v3/events/search', $params);
    }

    /**
     * Perform a GET request
     *
     * @param $resource
     * @param array $params
     * @return mixed
     * @throws \HttpException
     */
    public function get($resource, array $params = [])
    {
        $url = $this->makeUrl($resource, $params);

        $response = $this->client->get($url);

        return $this->parse($response);
    }

    /**
     * Perform a POST request
     *
     * @param $resource
     * @param array $fields
     * @param array $files
     * @return mixed
     * @throws \HttpException
     */
    public function post($resource, array $fields = [], array $files = [])
    {
        $url = $this->makeUrl($resource);

        $response = $this->client->post($url, [
            'form_fields' => $fields,
            'form_files'  => $files
        ]);

        return $this->parse($response);
    }

    /**
     * Make the request URL
     *
     * @param $resource
     * @param array $params
     * @return string
     */
    protected function makeUrl($resource, array $params = [])
    {
        // Make url
        $url = static::API_URL . trim($resource, '/');

        // Add query params
        $params = array_merge($params, ['token' => $this->token]);
        $url .= '?' . http_build_query($params);

        return $url;
    }

    /**
     * Parse the response
     *
     * @param ResponseInterface $response
     * @return mixed
     * @throws \HttpException
     */
    protected function parse(ResponseInterface $response)
    {
        $json = json_decode($response->getBody(), true);

        // Check for failure
        if ($response->getStatusCode() !== 200) {
            throw new \HttpException($json->error . ': ' . $json->error_description);
        }

        return $json;
    }
}
