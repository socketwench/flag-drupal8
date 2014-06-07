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
class FlagViewsRelationship extends RelationshipPluginBase {

  public function defineOptions() {
    $options = parent::defineOptions();
    $options['flag'] = array('default' => NULL); // @todo load first defined flag for entity.
    $options['required'] = array('default' => 1);
    $options['user_scope'] = array('default' => 'current');
    return $options;
  }

  public function buildOptionsForm(&$form, &$form_state) {
    $entity_type = $this->definition['flaggable'];
    //$form['label']['#description'] .= ' ' . t('The name of the selected flag makes a good label.');

    /*//////////////////////////////////////////////////////////////////////////
    @todo Add Flag selection form

    The Flag relationship relates a single flag to a single entity. Since
    multiple flags may be configured for the same entity type, we need to
    provide a form here that allows us to choose the flag.
    //////////////////////////////////////////////////////////////////////////*/
    $flags = \Drupal::service('flag')->getFlags($entity_type);

    $form['flag'] = array(
      '#type' => 'radios',
      '#title' => t('Flag'),
 //     '#default_value' => current(array_keys($flags)),
      '#required' => TRUE,
    );

    foreach ($flags as $fid => $flag) {
      if (!empty($flag)) {
        $form['flag']['#options'][$flag->label()] = $fid;
      }
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

  public function query() {
    if (!($flag = $this->getFlag())) {
      return;
    }

    $this->definition['extra'][] = array(
      'field' => 'fid',
      'value' => $flag->id,
      'numeric' => TRUE,
    );

    if ($this->options['user_scope'] == 'current' && !$flag->isGlobal()) {
      $this->definition['extra'][] = array(
        'field' => 'uid',
        'value' => '***CURRENT_USER***',
        'numeric' => TRUE,
      );
      $flag_roles = user_roles(FALSE, "flag $flag->name");
      if (isset($flag_roles[DRUPAL_ANONYMOUS_RID])) {
        // Disable page caching for anonymous users.
        drupal_page_is_cacheable(FALSE);

        // Add in the SID from Session API for anonymous users.
        $this->definition['extra'][] = array(
          'field' => 'sid',
          'value' => '***FLAG_CURRENT_USER_SID***',
          'numeric' => TRUE,
        );
      }
    }

//    parent::query();

  }

  public function getFlag() {
    $flaggable = $this->definition['flaggable'];
    $flag = \Drupal::service('flag')->getFlags($flaggable);
    $this->options['flag'] = $flag;
    return current($flag);
  }
}