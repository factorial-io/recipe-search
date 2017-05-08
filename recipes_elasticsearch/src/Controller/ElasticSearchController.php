<?php

namespace Drupal\recipes_elasticsearch\Controller;

use Drupal\Core\Url;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ElasticSearchController.
 *
 * @package Drupal\elastic_recipes\Controller
 */
class ElasticSearchController extends ControllerBase {

  protected $client;

  /**
   * {@inheritdoc}
   */
  public function __construct(ElasticSearchClient $client) {

    $this->client = $client->getClient();

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('recipes_elasticsearch.elasticsearchclient')
    );
  }

  /**
   * Page controller.
   */
  public function page(Request $request, $term = NULL) {

    return !empty($term) ? $this->search($term) : ['markup' => ''];
  }

  /**
   * Returns a page title.
   */
  public function getTitle(Request $request) {
    return t('Search result for %term', ['%term' => $request->get('term')]);
  }

  /**
   * Search.
   *
   * @return string
   *   Return styled search result.
   */
  public function search($user_input, $autocomplete = FALSE) {

    // The keywords typed in by the user.
    $input = strtolower($user_input);

    // https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-top-hits-aggregation.html
    // return 10 results per entity type, for 2 entity types (default is 3)
    $query = [
      'index' => 'recipes_index',
      'body' => [
        'query' => [
          'match' => [
            '_all' => $input,
          ],
        ],
        'aggs' => [
          'per_type' => [
            'terms' => [
              'field' => 'type',
              "size" => 2,
              'order' => [
                'top_hit' => 'desc',
              ],
            ],
            'aggs' => [
              'top_tags_hits' => [
                'top_hits' => [
                  "size" => 10,
                  '_source' => [
                    'includes' => [
                      'title', 'type', 'id', 'entity_type',
                    ],
                  ],
                ],
              ],
              'top_hit' => [
                'max' => [
                  'script' => [
                    'lang' => 'painless',
                    'inline' => '_score',
                  ],
                ],
              ],
            ],
          ],
        ],
      ],
    ];

    $response = $this->client->search($query);

    if ($autocomplete) {
      return $response;
    }

    $search_results = array(
      '#type' => 'table',
      '#header' => array(t('Title'), t('Type'), t('Link')),
      '#empty' => t('There are no items yet.'),
    );

    $id = 0;
    foreach ($response['aggregations']['per_type']['buckets'] as $bucket) {

      foreach ($bucket['top_tags_hits']['hits']['hits'] as $result) {

        ++$id;

        $search_results[$id]['title'] = array(
          '#plain_text' => $result['_source']['title'],
        );

        $search_results[$id]['type'] = array(
          '#plain_text' => $result['_source']['type'],
        );

        // @todo extend to other entity types
        $options = ['absolute' => TRUE];

        // Build the url for view & edit of entity_type.
        if ($result['_source']['entity_type'] == 'taxonomy_term') {
          $url = Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $result['_source']['id']], $options);
          $url_edit = Url::fromRoute('entity.taxonomy_term.edit_form', ['taxonomy_term' => $result['_source']['id']], $options);
        }
        else {
          $url = Url::fromRoute('entity.node.canonical', ['node' => $result['_source']['id']], $options);
          $url_edit = Url::fromRoute('entity.node.edit_form', ['node' => $result['_source']['id']], $options);
        }

        $search_results[$id]['operations'] = array(
          '#type' => 'operations',
          '#links' => array(),
        );

        $search_results[$id]['operations']['#links']['view'] = array(
          'title' => $this->t('view'),
          'url' => $url,
        );

        $search_results[$id]['operations']['#links']['edit'] = array(
          'title' => $this->t('edit'),
          'url' => $url_edit,
        );

      }
    }


    return $search_results;
  }

  /**
   * Suggest.
   *
   * @return string
   *   Return Hello string.
   */
  public function autocomplete(Request $request) {
    $input = strtolower($request->query->get('q'));

    $data = [];

    if ($input) {
      $response = $this->search($input, TRUE);

      if ($response['hits']['hits']) {
        foreach ($response['aggregations']['per_type']['buckets'] as $bucket) {
          foreach ($bucket['top_tags_hits']['hits']['hits'] as $hit) {

            $label = $hit['_source']['type'] . " | " . $hit['_source']['title'];
            $data[] = array('value' => $hit['_source']['title'], 'label' => $label);

          }
        }
      }
    }

    return new JsonResponse($data);
  }


  /**
   * Helper function used for testing EL functionality.
   *
   * Http://ph-backend.dev/elastic_recipes/debug?q=rain
   * http://ph-backend.dev:9200/recipes_index/_mapping?pretty.
   *
   * @return string
   *   Return Hello string.
   */
  public function debug(Request $request) {
    $input = strtolower($request->query->get('q'));

    if ($input) {
      try {


        // https://www.elastic.co/guide/en/elasticsearch/reference/current/fielddata.html
        $query = [
          'index' => 'recipes_index',
          'explain' => true,
          'body' => [
            'aggs' => [
              'recipe_type' => [
                "filter" => [
                  "term" => [
                    "type" => "recipe",
                  ],
                ],
                'aggs' => [
                  'recipe' => [
                    'terms' => [
                      'field' => 'field_recipe_ingredients.keyword_ingredients',
                    ],
                  ],
                ],
              ],
              'fr_recipe_type' => [
                "filter" => [
                  "term" => [
                    "type" => "factorial_recipe",
                  ],
                ],
                'aggs' => [
                  'recipe' => [
                    'terms' => [
                      'field' => 'field_fr_ingredients.keyword_ingredients',
                    ],
                  ],
                ],
              ],
            ],
          ],
        ];


        $response = $this->client->search($query);


        if ($response['hits']['hits']) {
          $data['recipe'] = [];
          foreach ($response['aggregations']['recipe_type']['recipe']['buckets'] as $ingredient) {
            $data['recipe'][] = array('value' => $ingredient['key'], 'label' => $ingredient['doc_count']);
          }
          $data['factorial_recipe'] = [];
          foreach ($response['aggregations']['fr_recipe_type']['recipe']['buckets'] as $ingredient) {
            $data['factorial_recipe'][] = array('value' => $ingredient['key'], 'label' => $ingredient['doc_count']);
          }

        }




        kint($data); die();

      }
      catch (\Exception $e) {
        kint($e);
      }

    }

    return new JsonResponse($data);
  }

}
