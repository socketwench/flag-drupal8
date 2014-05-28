<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 5/11/14
 * Time: 9:50 PM
 */

namespace Drupal\flag\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Class FlagViewsLinkField
 * @package Drupal\flag\Plugin\views\field
 *
 * @ViewsField("flag_link")
 */
class FlagViewsLinkField extends FieldPluginBase {

  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['text'] = array(
      'default' => '',
      'translatable' => TRUE,
    );
/*
    $options['link_to_entity'] = array(
      'default' => FALSE,
      'bool' => TRUE,
    );
*/
    //@todo return link type

    return $options;
  }

  /**
   * @param $form
   * @param $form_state
   */
  public function buildOptionsForm(&$form, &$form_state) {
    $form['text'] = array(
      '#type' => 'textfield',
      '#title' => t('Text to display'),
      '#default_value' => $this->options['text'],
    );

    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * @param ResultRow $values
   * @return string|void
   */
  public function render(ResultRow $values) {
    $comment = $this->getEntity($values);
    return $this->renderLink($comment, $values);
  }

  /**
   * @param $data
   * @param ResultRow $values
   */
  protected function renderLink($data, ResultRow $values) {
    if ($node->access('view')) {
      $text = !empty($this->options['text']) ? $this->options['text'] : t('View');
      return $text;
    }
  }

} 