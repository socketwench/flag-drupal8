<?php
/**
 * @file
 * Contains \Drupal\flag\Plugin\views\field\FlagViewsFlaggedField.
 */

namespace Drupal\flag\Plugin\views\field;

use Drupal\views\ViewExecutable;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\field\Boolean;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a views field to show if the selected content is flagged or not.
 *
 * This field differs from FlagViewsLinkField in that it is display only. It
 * does not provide an actionable link, but rather inherits from the Boolean
 * views field handler.
 *
 * @ViewsField("flag_flagged")
 */
class FlagViewsFlaggedField extends Boolean {

  // @todo: Define the FlagViewsFlaggedField::formats variable?
  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);

    // Add our boolean labels.
    $this->formats['flag'] = [t('Flagged'), t('Not flagged')];
    // TODO: We could probably lift the '(Un)Flagged message' strings from the
    // flag object, but a) we need to lift that from the relationship we're on
    // and b) they will not necessarily make sense in a static context.
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['relationship'] = ['default' => 'flag_content_rel'];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $form['relationship']['#default_value'] = $this->options['relationship'];

    parent::buildOptionsForm($form, $form_state);
  }
}
