<?php
/**
 * @file
 * Contains the FlagViewsFlaggedField class.
 */

namespace Drupal\flag\Plugin\views\field;

use Drupal\views\ViewExecutable;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\field\Boolean;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class FlagViewsFlaggedField
 * @package Drupal\flag\Plugin\views\field
 *
 * @ViewsField("flag_flagged")
 */
class FlagViewsFlaggedField extends Boolean {

  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);

    // Add our boolean labels.
    $this->formats['flag'] = array(t('Flagged'), t('Not flagged'));
    // TODO: We could probably lift the '(Un)Flagged message' strings from the
    // flag object, but a) we need to lift that from the relationship we're on
    // and b) they will not necessarily make sense in a static context.
  }

  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['relationship'] = array('default' => 'flag_content_rel');

    return $options;
  }

  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $form['relationship']['#default_value'] = $this->options['relationship'];

    parent::buildOptionsForm($form, $form_state);
  }
}