<?php

namespace Drupal\recipes_elasticsearch\Plugin\ElasticsearchIndex;

use Drupal\elasticsearch_helper\Plugin\ElasticsearchIndexBase;

/**
 * Index taxonomy.
 *
 * @ElasticsearchIndex(
 *   id = "recipes_term_index",
 *   label = @Translation("Recipes Term Index"),
 *   indexName = "recipes_term_index",
 *   typeName = "taxonomy_term",
 *   entityType = "taxonomy_term"
 * )
 */
class RecipesTermIndex extends ElasticsearchIndexBase {

  /**
   * NOTE:.
   *
   * The structure of the indexed data is determined by normalizers,
   * see NodeNormalizer.php.
   */

  /**
   * Determine the name of the type where the given data will be indexed.
   */
  protected function getTypeName($data) {
    return $data['type'];
  }

}
