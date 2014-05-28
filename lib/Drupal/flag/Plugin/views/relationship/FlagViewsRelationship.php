<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 5/13/14
 * Time: 8:46 PM
 */

namespace Drupal\flag\Plugin\views\relationship;

use Drupal\views\Plugin\views\relationship\RelationshipPluginBase;

/**
 * Class FlagViewsRelationship
 *
 * @ViewsRelationship("flag_relationship")
 */
public class FlagViewsRelationship extends RelationshipPluginBase {

  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['flag'] = array('default' => NULL);
    $options['required'] = array('default' => 1);
    $options['user_scope'] = array('default' => 'current');
    return $options;
  }

  protected function buildOptionsForm(&$form, &$form_state) {
    $entity_type = $this->definition['flag type'];
    $form['label']['#description'] .= ' ' . t('The name of the selected flag makes a good label.');
    $form['flag'] = array(
      '#type' => $form_type,
      '#title' => t('Flag'),
      '#default_value' => $current_flag,
      '#required' => TRUE,
    );

    $flags = \Drupal::service('flag')->getFlags($entity_type)
    foreach ($flags as $flag) {
      $form['flag']['#options'][$flag->label()] = $flag->id();
    }

    $form['user_scope'] = array(
      '#type' => 'radios',
      '#title' => t('By'),
      '#options' => array('current' => t('Current user'), 'any' => t('Any user')),
      '#default_value' => $this->options['user_scope'],
    );

    $form['required']['#title'] = t('Include only flagged content');
    $form['required']['#description'] = t('If checked, only content that has this flag will be included. Leave unchecked to include all content; or, in combination with the <em>Flagged</em> filter, <a href="@unflagged-url">to limit the results to specifically unflagged content</a>.', array('@unflagged-url' => 'http://drupal.org/node/299335'));

    if (!$form['flag']['#options']) {
      $form = array(
        'error' => array(
          '#markup' => '<p class="error form-item">' . t('No %type flags exist. You must first <a href="@create-url">create a %type flag</a> before being able to use this relationship type.', array('%type' => $entity_type, '@create-url' => url(FLAG_ADMIN_PATH))) . '</p>',
        ),
      );
      $form_state['no flags exist'] = TRUE;
    }

    parent::buildOptionsForm($form, $form_state);
  }

  public query() {
    $this->ensureMyTable();

    $def = $this->definition;
    $def['table'] = 'flag';
  }
}