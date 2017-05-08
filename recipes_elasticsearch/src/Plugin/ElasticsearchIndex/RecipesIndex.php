<?php

namespace Drupal\recipes_elasticsearch\Plugin\ElasticsearchIndex;

use Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexBase;

/**
 * @ElasticsearchIndex(
 *   id = "recipes_index",
 *   label = @Translation("Recipes Index"),
 *   indexName = "recipes_index",
 *   typeName = "node",
 *   entityType = "node"
 * )
 */
class RecipesIndex extends ElasticsearchIndexBase {

  /**
   * NOTE:.
   *
   * The structure of the indexed data is determined by normalizers,
   * see NodeNormalizer.php.
   */

  /**
   * Determine the name of the type where the given data will be indexed.
   *
   * @return string
   */
  protected function getTypeName($data) {
    return $data['type'];
  }

}
