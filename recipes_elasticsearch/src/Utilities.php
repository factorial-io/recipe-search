<?php

namespace Drupal\recipes_elasticsearch;

use Drupal\Core\Entity\EntityInterface;
use Drupal\taxonomy\Entity\Term;

/**
 * Utilities class for elastic search indexing.
 */
class Utilities {

  /**
   * Get the field value.
   *
   * @var EntityInterface $object
   */
  protected static function getFieldValue($object, $field_type, $field_name) {

    $irrelevant_ingredients = ['to', 'and', 'or', 'of', 'for', 'with', 'cup', 'cups',
      '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'bunch', 'ounce', 'ounces',
      'tablespoon', 'tablespoons', 'tablespons', 'tbsp', 'tbs', 'teaspoon', 'teaspoons',
      'tsp', 'pinch', 'taste', 'none', 'warm', 'cold', 'whole', 'weight', 'clove',
      'cloves', 'pound', 'can', 'cans', 'package', 'slices', 'chopped', 'stick',
      'sticks', 'ground', 'large', 'fluid', 'fresh', 'dash', 'piece', 'pieces', 'box'];



    if ($field_type === 'ingredient') {
      $field_val = array_map(function($ingredient) {
        return $ingredient['value'];
      }, $object->get($field_name)->getValue());

      // prepare for indexing
      $ingredient_list = implode(", ", $field_val);
      $ingredients = array_filter(explode(" ", $ingredient_list));
      $ingredients = str_replace(",", "", str_replace($irrelevant_ingredients, "", $ingredients));
      $term_arr = [
        'ingredients' => $ingredient_list,
        'keyword_ingredients' => $ingredients,
      ];

      $field_val = $term_arr;
    }
    elseif ($field_type === 'number') {
      $field_val = (int) $object->get($field_name)->value;
      $field_val = !is_null($field_val) ? (int) $field_val : '';
    }
    else {
      $field_val = $object->get($field_name)->value;
      $field_val = !is_null($field_val) ? (string) $field_val : '';
    }

    return $field_val;
  }

  /**
   * Helper function, get terms.
   */
  protected static function getTerms(EntityInterface $object, $field_name) {
    $term_arr = [];

    if ($object->hasField($field_name)) {
      $terms = $object->get($field_name)->getValue();

      foreach ($terms as $term) {
        $term_name = Term::load($term['target_id'])->getName();
        $tid = $term['target_id'];
        $term_arr[] = [
          'name' => $term_name,
          'id' => $tid,
        ];
      }
    }
    return $term_arr;
  }

  /**
   * Construct the data to be stored in the index.
   */
  public static function getFieldsFromConfig(EntityInterface $object, array $config) {

    $content_type = $object->bundle();
    $data = [];
    if (isset($config[$content_type])) {
      // array_map will require key and value hence using foreach.
      foreach ($config[$content_type] as $field_name => $field_type) {
        // Extend this for more complicated scenario.
        $field_value = self::getFieldValue($object, $field_type, $field_name);
        if ($field_value) {
          $data[$field_name] = $field_value;
        }
      }
    }

    return $data;
  }

}
