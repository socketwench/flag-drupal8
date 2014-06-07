<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 6/7/14
 * Time: 5:28 PM
 */

namespace Drupal\flag\Plugin\views\field;

use Drupal\views\Plugin\views\field\Boolean;

/**
 * Class FlagViewsFlaggedField
 * @package Drupal\flag\Plugin\views\field
 *
 * @ViewsField("flag_flagged")
 */
class FlagViewsFlaggedField extends Boolean {

  public function defineOptions() {
    $options = parent::defineOptions();
    $options['value'] = array('default' => 1);
    return $options;
  }

  public function buildOptionsForm(&$form, &$form_state) {
    parent::buildOptionsForm($form, $form_state);
    $form['value']['#type'] = 'radios';
    $form['value']['#title'] = t('Status');
    $form['value']['#options'] = array(
      1 => t('Flagged'),
      0 => t('Not flagged'),
      'All' => t('All'),
    );
    $form['value']['#default_value'] = empty($this->options['value']) ? '0' : $this->options['value'];
    $form['value']['#description'] = '<p>' . t('This filter is only needed if the relationship used has the "Include only flagged content" option <strong>unchecked</strong>. Otherwise, this filter is useless, because all records are already limited to flagged content.') . '</p><p>' . t('By choosing <em>Not flagged</em>, it is possible to create a list of content <a href="@unflagged-url">that is specifically not flagged</a>.', array('@unflagged-url' => 'http://drupal.org/node/299335')) . '</p>';
  }

  public function query() {
    $operator = $this->value ? 'IS NOT' : 'IS';
    $this->query->addWhere($this->options['group'], $this->relationship . '.uid', NULL, $operator . ' NULL');
  }
}