<?php

namespace Drupal\fountains_gifts_api;

/**
 * @package Drupal\fountains_gifts_api
 */
class InternalClient {

  protected $backendName;

  protected $client;

  protected $authToken;

  public function __construct($backend = '', $scope = '') {
    $this->backendName = $backend;
    $this->client = $this->initClient();

    if ($backend) {
      $this->authToken = $this->getAuthToken($backend, $scope);
    }
  }

  protected  function getBaseUrl() {
    return \Drupal::config('routes')->get('backend-api-base')['url'];
  }

  protected function initClient() {
    return \Drupal::service('http_client_factory')->fromOptions([
      'base_uri' => $this->getBaseUrl(),
    ]);
  }

  protected function getAuthToken($backend, $scope) {
    $state_key = 'fountains_api.auth_token.' . $backend_name;
    $storedToken = \Drupal::state()->get($state_key);

    if ($storedToken) {
      if ($storedToken['expires'] > REQUEST_TIME) {
        return $storedToken['access_token'];
      }
    }

    $credentials = \Drupal::config('routes')->get($backend_name);
    if (empty($credentials['client_id'])) {
      return '';
    }

    // Fetch internal token.
    $response = $this->request('POST', '/v1/oauth/token', [
      'form_params' => [
        'grant_type' => 'client_credentials',
        'client_id' => $credentials['client_id'],
        'client_secret' => $credentials['client_secret'],
        'scope' => $scope,
      ],
    ]);

    $data = \GuzzleHttp\json_decode($response->getBody(), TRUE);

    if ($data['access_token']) {

      $data['expires'] = REQUEST_TIME + $data['expires_in'];
      \Drupal::state()->set($state_key, $data);

      return $data['access_token'];
    }

    return '';

  }
  public function request($type, $path, $guzzle_options = []) {

    if ($this->authToken) {
      $guzzle_options['headers']['Authorization'] = 'Bearer ' . $this->authToken;
    }

    return $this->client->request($type, $path, $guzzle_options);
  }

  public function get($path, $guzzle_options = []) {
    $response = $this->request('GET', $path, $guzzle_options);
    return \GuzzleHttp\json_decode($response->getBody(), TRUE);
  }

}
