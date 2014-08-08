<?php
/**
 * @file
 * Contains the FlagViewsLinkField class.
 */

namespace Drupal\flag\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class FlagViewsLinkField
 * @package Drupal\flag\Plugin\views\field
 *
 * @ViewsField("flag_link")
 */
class FlagViewsLinkField extends FieldPluginBase {

  public function getFlag() {
    // When editing a view it's possible to delete the relationship (either by
    // error or to later recreate it), so we have to guard against a missing
    // one.
    if (isset($this->view->relationship[$this->options['relationship']])) {
      return $this->view->relationship[$this->options['relationship']]->getFlag();
    }
  }

  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['text'] = array(
      'default' => '',
      'translatable' => TRUE,
    );

    // Set the default relationship handler. The first instance of the
    // FlagViewsRelationship should always have the id "flag_content_rel", so
    // we set that as the default.
    $options['relationship'] = array('default' => 'flag_content_rel');

    return $options;
  }

  /**
   * @param $form
   * @param $form_state
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $form['text'] = array(
      '#type' => 'textfield',
      '#title' => t('Text to display'),
      '#default_value' => $this->options['text'],
    );

    $form['relationship']['#default_value'] = $this->options['relationship'];

    parent::buildOptionsForm($form, $form_state);
  }

  public function query() {
    // Intentionally do nothing here since we're only providing a link and not
    // querying against a real table column.
  }

  /**
   * @param ResultRow $values
   * @return string|void
   */
  public function render(ResultRow $values) {
    //$entity = $this->getEntity($values);
    return $this->renderLink($values->_entity, $values);
  }

  /**
   * @param $data
   * @param ResultRow $values
   */
  protected function renderLink($entity, ResultRow $values) {
    // if (empty($entity) || !$entity->access('view')) {
    if (empty($entity)) {
      return t('N/A');
    }

    $flag = $this->getFlag();
    $linkTypePlugin = $flag->getLinkTypePlugin();
    $action = 'flag';

    if ($flag->isFlagged($entity)) {
      $action = 'unflag';
    }

    $link = $linkTypePlugin->renderLink($action, $flag, $entity);

    return $link;
  }

} 