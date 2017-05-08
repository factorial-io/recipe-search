<?php

/**
 * @file
 */

/**
 * Implementation of hook_drush_command().
 */
function recipes_init_drush_command() {
  $items['init-recipes'] = array(
    'description' => 'Import content into drupal.',
  );

  $items['delete-recipes'] = array(
    'description' => 'Delete recipe data from drupal.',
  );

  return $items;
}

/**
 * Drush command to import recipes into drupal.
 */
function drush_recipes_init_init_recipes() {
  _recipes_init_import();
}

/**
 * Drush command to delete recipes from drupal.
 */
function drush_recipes_init_delete_recipes() {
  _delete_content();
}
