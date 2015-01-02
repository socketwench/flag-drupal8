<?php
/**
 * @file
 * Contains \Drupal\flag\Plugin\views\field\FlagViewsLinkField.
 */

namespace Drupal\flag\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a views field to flag or unflag the selected content.
 *
 * Unlike FlagViewsFlaggedField, this views field handler provides an
 * actionable link to flag or unflag the selected content.
 *
 * @ViewsField("flag_link")
 */
class FlagViewsLinkField extends FieldPluginBase {

  /**
   * A helper method to retrieve the flag entity from the views relationship.
   *
   * @return FlagInterface|null
   *   The flag selected by the views relationship.
   */
  public function getFlag() {
    // When editing a view it's possible to delete the relationship (either by
    // error or to later recreate it), so we have to guard against a missing
    // one.
    if (isset($this->view->relationship[$this->options['relationship']])) {
      return $this->view->relationship[$this->options['relationship']]->getFlag();
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['text'] = [
      'default' => '',
      'translatable' => TRUE,
    ];

    // Set the default relationship handler. The first instance of the
    // FlagViewsRelationship should always have the id "flag_content_rel", so
    // we set that as the default.
    $options['relationship'] = array('default' => 'flag_content_rel');

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $form['text'] = [
      '#type' => 'textfield',
      '#title' => t('Text to display'),
      '#default_value' => $this->options['text'],
    ];

    $form['relationship']['#default_value'] = $this->options['relationship'];

    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Intentionally do nothing here since we're only providing a link and not
    // querying against a real table column.
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    // $entity = $this->getEntity($values);
    return $this->renderLink($values->_entity, $values);
  }

  /**
   * Creates a render array for flag links.
   *
   * @param EntityInterface $entity
   *   The entity object.
   * @param ResultRow $values
   *   The current result row.
   *
   * @return array
   *   The render array for the flag link.
   */
  protected function renderLink(EntityInterface $entity, ResultRow $values) {
    if (empty($entity)) {
      return t('N/A');
    }

    $flag = $this->getFlag();
    $link_type_plugin = $flag->getLinkTypePlugin();
    $action = 'flag';

    if ($flag->isFlagged($entity)) {
      $action = 'unflag';
    }

    $link = $link_type_plugin->renderLink($action, $flag, $entity);

    return $link;
  }

}
