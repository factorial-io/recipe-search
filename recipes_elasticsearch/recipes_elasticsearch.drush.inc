<?php

use Elasticsearch\ClientBuilder;
use Drush\Log\LogLevel;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Drupal\taxonomy\Entity\Term;


/**
 * Implementation of hook_drush_command().
 */
function recipes_elasticsearch_drush_command()
{
  $items['index-recipes'] = array(
      'description' => 'Import content into elastic search.',
  );

  return $items;
}

/**
 * Drush command to import recipes into elasticsearch.
 */
function drush_recipes_elasticsearch_index_recipes()
{
  recipes_elasticsearch_create_index();
  recipes_elasticsearch_import();
  drush_print('Imported your content');
}
