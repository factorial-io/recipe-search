<?php

namespace Drupal\recipes_elasticsearch\Plugin\Normalizer;

use Drupal\serialization\Normalizer\ContentEntityNormalizer;
use Drupal\recipes_elasticsearch\Utilities;

/**
 * Normalizes / denormalizes Drupal nodes into an array structure good for ES.
 */
class NodeNormalizer extends ContentEntityNormalizer {

  /**
   * Mapping of entity fields elastic_recipes.mapping.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private $config;


  /**
   * The interface or class that this Normalizer supports.
   *
   * @var array
   */
  protected $supportedInterfaceOrClass = ['Drupal\node\Entity\Node'];

  /**
   * Supported formats.
   *
   * @var array
   */
  protected $format = ['elasticsearch_helper'];

  /**
   * {@inheritdoc}
   *
   * Structures the data for a single node, this data will be passed
   * to elastic search for indexing.
   *
   * @var \Drupal\node\Entity\Node $object
   */
  public function normalize($object, $format = NULL, array $context = array()) {
    $this->fieldMapping();

    // 'body' => $object->body->value,.
    $data = [
      'id' => $object->id(),
      'uuid' => $object->uuid(),
      'title' => $object->getTitle(),
      // Bundle.
      'type' => $object->getType(),
      'status' => $object->isPublished(),
      'user' => [
        'name' => $object->getRevisionAuthor()->getAccountName(),
        'id' => $object->getRevisionAuthor()->id(),
      ],
      'entity_type' => 'node',
    ];

    // Add fields from configuration file
    // elastic_recipes.mapping.
    $data += Utilities::getFieldsFromConfig($object, $this->config);

    return $data;
  }

  /**
   * Returns $extra_fields for node normalizer.
   */
  public function fieldMapping() {

    if (!isset($this->config)) {
      $this->config = \Drupal::config('recipes_elasticsearch.mapping')->get('content_type');
    }

  }

}
