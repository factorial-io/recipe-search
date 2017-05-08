<?php

namespace Drupal\recipes_elasticsearch\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'ElasticSearchBlock' block.
 *
 * @Block(
 *  id = "elastic_search_block",
 *  admin_label = @Translation("Elastic search block"),
 * )
 */
class ElasticSearchBlock extends BlockBase
{

  /**
   * {@inheritdoc}
   */
  public function build()
  {

    $user = \Drupal::currentUser();

    // return an empty block if user cant search
    $block = $user->hasPermission('search elastic content') ? \Drupal::formBuilder()->getForm('Drupal\recipes_elasticsearch\Form\ElasticSearchForm') : [];

    return $block;
  }
}
