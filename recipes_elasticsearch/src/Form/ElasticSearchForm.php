<?php

namespace Drupal\recipes_elasticsearch\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Component\Utility\Xss;

/**
 * Class ElasticSearchForm.
 *
 * @package Drupal\elastic_recipes\Form
 */
class ElasticSearchForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'elastic_search_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['elastic_search'] = array(
      '#type' => 'textfield',
      '#prefix' => '',
      '#suffix' => '',
      '#default_value' => $this->getRequest()->get('term'),
      '#placeholder' => t('Search'),
      '#autocomplete_route_name' => 'recipes_elasticsearch.elastic_search_controller_autocomplete',
      '#autocomplete_route_parameters' => array(),
    );

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Search'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $input = $form_state->getValue('elastic_search');
    // Add conditional redirect
    // Xss strip.
    $term = Xss::filter($input);

    // Validate user has permission.
    $url = Url::fromUri('internal:/search/' . $term);

    // Xss stip.
    $form_state->setRedirectUrl($url);

  }

}
