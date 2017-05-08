<?php

namespace Drupal\recipes_elasticsearch\Controller;

use Drupal\Core\Controller\ControllerBase;
use Elasticsearch\ClientBuilder;

/**
 * Class ElasticSearchController.
 *
 * @package Drupal\elastic_recipes\Controller
 */
class ElasticSearchClient extends ControllerBase {
  /**
   * @var \Elasticsearch\Client
   */
  protected $client;

  /**
   *
   */
  public function __construct() {

    $config = \Drupal::service('config.factory')->getEditable('elasticsearch_helper.settings');
    $host = $config->get('elasticsearch_helper.host');
    $port = $config->get('elasticsearch_helper.port');

    $this->client = ClientBuilder::fromConfig([
      'hosts' => ["$host:$port"],
    ]);
  }

  /**
   *
   */
  public function getClient() {
    return $this->client;
  }

}
