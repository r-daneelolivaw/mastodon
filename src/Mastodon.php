<?php

namespace Drupal\mastodon;

use Colorfield\Mastodon\MastodonOAuth;
use Colorfield\Mastodon\MastodonAPI;
use Colorfield\Mastodon\UserVO;

/**
 * Class Mastodon.
 *
 * @todo type responses
 */
class Mastodon implements MastodonInterface {

  /**
   * MastodonOAuth definition.
   *
   * @var \Colorfield\Mastodon\MastodonOAuth
   */
  private $oAuth;

  /**
   * MastodonAPI definition.
   *
   * @var \Colorfield\Mastodon\MastodonAPI
   */
  private $api;

  /**
   * Constructs a new Mastodon object based on the configuration.
   */
  public function __construct() {
    // @todo DI of config.factory
    $mastodonConfig = \Drupal::config('mastodon.settings');
    $auth = [];
    $auth['name'] = $mastodonConfig->get('application_name');
    $auth['instance'] = $mastodonConfig->get('mastodon_instance');
    $auth['client_id'] = $mastodonConfig->get('client_id');
    $auth['client_secret'] = $mastodonConfig->get('client_secret');
    $auth['bearer'] = $mastodonConfig->get('bearer');
    // @todo website
    // $mastodonConfig['website'] = '';
    foreach ($auth as $key => $config) {
      if (empty($config)) {
        drupal_set_message(
          t('Missing Mastodon OAuth configuration for @config',
            ['@config' => $key]
          ), 'error'
        );
      }
    }
    $this->oAuth = new MastodonOAuth($auth['name'], $auth['instance']);
    $this->oAuth->config->setClientId($auth['client_id']);
    $this->oAuth->config->setClientSecret($auth['client_secret']);
    $this->oAuth->config->setBearer($auth['bearer']);
    $this->api = new MastodonAPI($this->oAuth->config);
  }

  /**
   * {@inheritdoc}
   */
  public function getApi() {
    return $this->api;
  }

  /**
   * {@inheritdoc}
   */
  public function authenticateUser($email, $password) {
    $this->oAuth->authenticateUser($email, $password);
    $credentials = $this->api->get('/accounts/verify_credentials');
    $user = new UserVO($credentials);
    return $user;
  }

  /**
   * Gets an account's followers.
   *
   * @param int $user_id
   *   Mastodon user id.
   * @param array $params
   *   Optional parameters: max_id, since_id, limit.
   *
   * @return array
   *   Array of Accounts.
   */
  public function getFollowers($user_id, array $params = []) {
    return $this->api->get('/accounts/' . $user_id . '/followers', $params);
  }

  /**
   * Gets who is following an account.
   *
   * @param int $user_id
   *   Mastodon user id.
   * @param array $params
   *   Optional parameters: max_id, since_id, limit.
   *
   * @return array
   *   Array of Accounts.
   */
  public function getFollowing($user_id, array $params = []) {
    return $this->api->get('/accounts/' . $user_id . '/following', $params);
  }

  /**
   * Gets an account's statuses.
   *
   * @param int $user_id
   *   Mastodon user id.
   * @param array $params
   *   Optional parameters: only_media, exclude_replies, max_id,
   *   since_id, limit.
   *
   * @return array
   *   Array of Statuses.
   */
  public function getStatuses($user_id, array $params = []) {
    return $this->api->get('/accounts/' . $user_id . '/statuses', $params);
  }

  /**
   * Gets an account's relationships.
   *
   * @param array $user_ids
   *   Array of user ids.
   *
   * @return array
   *   Array of Relationships of the current user.
   */
  public function getRelationships(array $user_ids) {
    return $this->api->get('/accounts/relationships', $user_ids);
  }

  /**
   * Searches for accounts.
   *
   * @param array $params
   *   Mandatory: q (what to search for), optional: limit.
   *
   * @return array
   *   Array of matching Accounts.
   */
  public function search(array $params) {
    return $this->api->get('/accounts/search', $params);
  }

}
