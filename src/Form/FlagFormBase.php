<?php
/**
 * @file
 * Contains the FlagFormBase class.
 */

namespace Drupal\flag\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\flag\Handlers\AbstractFlag;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the base flag add/edit form.
 *
 * Since both the add and edit flag forms are largely the same, the majority of
 * functionality is done in this class. It generates the form, validates the
 * input, and handles the submit.
 *
 * @package Drupal\flag\Form
 */
abstract class FlagFormBase extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $entity_type = NULL) {
    $form = parent::buildForm($form, $form_state);

    $flag = $this->entity;

    $form['#flag'] = $flag;
    $form['#flag_name'] = $flag->id;

    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => t('Label'),
      '#default_value' => $flag->label,
      '#description' => t('A short, descriptive title for this flag. It will be used in administrative interfaces to refer to this flag, and in page titles and menu items of some <a href="@insite-views-url">views</a> this module provides (theses are customizable, though). Some examples could be <em>Bookmarks</em>, <em>Favorites</em>, or <em>Offensive</em>.', array('@insite-views-url' => url('admin/structure/views'))),
      '#maxlength' => 255,
      '#required' => TRUE,
      '#weight' => -3,
    );

    $form['id'] = array(
      '#type' => 'machine_name',
      '#title' => t('Machine name'),
      '#default_value' => $flag->id,
      '#description' => t('The machine-name for this flag. It may be up to 32 characters long and may only contain lowercase letters, underscores, and numbers. It will be used in URLs and in all API calls.'),
      '#weight' => -2,
      '#machine_name' => array(
        'exists' => 'flag_load_by_id',
      ),
      '#disabled' => !$flag->isNew(),
      '#submit' => array(array($this, 'submitSelectPlugin')),
      '#required' => TRUE,
      '#executes_submit_callback' => TRUE,
      '#ajax' => array(
        'callback' => array($this, 'updateSelectedPluginType'),
        'wrapper' => 'monitoring-sensor-plugin',
        'method' => 'replace',
      ),
    );

    $form['is_global'] = array(
      '#type' => 'checkbox',
      '#title' => t('Global flag'),
      '#default_value' => $flag->isGlobal(),
      '#description' => t('If checked, flag is considered "global" and each entity is either flagged or not. If unchecked, each user has individual flags on entities.'),
      '#weight' => -1,
    );

    $form['messages'] = array(
      '#type' => 'fieldset',
      '#title' => t('Messages'),
    );

    $form['messages']['flag_short'] = array(
      '#type' => 'textfield',
      '#title' => t('Flag link text'),
      '#default_value' => !empty($flag->flag_short) ? $flag->flag_short : t('Flag this item'),
      '#description' => t('The text for the "flag this" link for this flag.'),
      '#required' => TRUE,
    );

    $form['messages']['flag_long'] = array(
      '#type' => 'textfield',
      '#title' => t('Flag link description'),
      '#default_value' => $flag->flag_long,
      '#description' => t('The description of the "flag this" link. Usually displayed on mouseover.'),
    );

    $form['messages']['flag_message'] = array(
      '#type' => 'textfield',
      '#title' => t('Flagged message'),
      '#default_value' => $flag->flag_message,
      '#description' => t('Message displayed after flagging content. If JavaScript is enabled, it will be displayed below the link. If not, it will be displayed in the message area.'),
    );

    $form['messages']['unflag_short'] = array(
      '#type' => 'textfield',
      '#title' => t('Unflag link text'),
      '#default_value' => !empty($flag->unflag_short) ? $flag->unflag_short : t('Unflag this item'),
      '#description' => t('The text for the "unflag this" link for this flag.'),
      '#required' => TRUE,
    );

    $form['messages']['unflag_long'] = array(
      '#type' => 'textfield',
      '#title' => t('Unflag link description'),
      '#default_value' => $flag->unflag_long,
      '#description' => t('The description of the "unflag this" link. Usually displayed on mouseover.'),
    );

    $form['messages']['unflag_message'] = array(
      '#type' => 'textfield',
      '#title' => t('Unflagged message'),
      '#default_value' => $flag->unflag_message,
      '#description' => t('Message displayed after content has been unflagged. If JavaScript is enabled, it will be displayed below the link. If not, it will be displayed in the message area.'),
    );

    $form['access'] = array(
      '#type' => 'fieldset',
      '#title' => t('Flag access'),
      '#tree' => FALSE,
      '#weight' => 10,
    );

    // Switch plugin type in case a different is chosen.

    $flag_type_plugin = $flag->getFlagTypePlugin();
    $flag_type_def = $flag_type_plugin->getPluginDefinition();

    $bundles = entity_get_bundles($flag_type_def['entity_type']);
    $entity_bundles = array();
    foreach ($bundles as $bundle_id => $bundle_row) {
      $entity_bundles[$bundle_id] = $bundle_row['label'];
    }

    // Flag classes will want to override this form element.
    $form['access']['types'] = array(
      '#type' => 'checkboxes',
      '#title' => t('Flaggable types'),
      '#options' => $entity_bundles,
      '#default_value' => $flag->types,
      '#description' => t('Check any sub-types that this flag may be used on.'),
      '#required' => TRUE,
      '#weight' => 10,
    );

    $form['access']['unflag_denied_text'] = array(
      '#type' => 'textfield',
      '#title' => t('Unflag not allowed text'),
      '#default_value' => $flag->unflag_denied_text,
      '#description' => t('If a user is allowed to flag but not unflag, this text will be displayed after flagging. Often this is the past-tense of the link text, such as "flagged".'),
      '#weight' => -1,
    );

    $form['display'] = array(
      '#type' => 'fieldset',
      '#title' => t('Display options'),
      '#description' => t('Flags are usually controlled through links that allow users to toggle their behavior. You can choose how users interact with flags by changing options here. It is legitimate to have none of the following checkboxes ticked, if, for some reason, you wish <a href="@placement-url">to place the the links on the page yourself</a>.', array('@placement-url' => 'http://drupal.org/node/295383')),
      '#tree' => FALSE,
      '#weight' => 20,
      // @todo: Move flag_link_type_options_states() into controller?
      // '#after_build' => array('flag_link_type_options_states'),
    );

    $form['display']['settings'] = array(
      '#type' => 'container',
      '#prefix' => '<div id="link-type-settings-wrapper">',
      '#suffix' => '</div>',
      '#weight' => 21,
    );

    $form = $flag_type_plugin->buildConfigurationForm($form, $form_state);

    $form['display']['link_type'] = array(
      '#type' => 'radios',
      '#title' => t('Link type'),
      '#options' => \Drupal::service('plugin.manager.flag.linktype')->getAllLinkTypes(),
      // '#after_build' => array('flag_check_link_types'),
      '#default_value' => $flag->getLinkTypePlugin()->getPluginId(),
      // Give this a high weight so additions by the flag classes for entity-
      // specific options go above.
      '#weight' => 18,
      '#attached' => array(
        'js' => array(drupal_get_path('module', 'flag') . '/theme/flag-admin.js'),
      ),
      '#attributes' => array(
        'class' => array('flag-link-options'),
      ),
      '#limit_validation_errors' => array(array('link_type')),
      '#submit' => array(array($this, 'submitSelectPlugin')),
      '#required' => TRUE,
      '#executes_submit_callback' => TRUE,
      '#ajax' => array(
        'callback' => array($this, 'updateSelectedPluginType'),
        'wrapper' => 'link-type-settings-wrapper',
        'event' => 'change',
        'method' => 'replace',
      ),
    );
    $form['display']['link_type_submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Update'),
      '#submit' => array(array($this, 'submitSelectPlugin')),
      '#weight' => 20,
      '#attributes' => array('class' => array('js-hide')),
    );
    // Add the descriptions to each ratio button element. These attach to the
    // elements when FormAPI expands them.
    $action_link_plugin_defs = \Drupal::service('plugin.manager.flag.linktype')->getDefinitions();
    foreach ($action_link_plugin_defs as $key => $info) {
      $form['display']['link_type'][$key]['#description'] = $info['description'];
      $form['display']['link_type'][$key]['#submit'] = array(array($this, 'submitSelectPlugin'));
      $form['display']['link_type'][$key]['#executes_submit_callback'] = TRUE;
      $form['display']['link_type'][$key]['#limit_validation_errors'] = array(array('link_type'));
    }

    $action_link_plugin = $flag->getLinkTypePlugin();
    $form = $action_link_plugin->buildConfigurationForm($form, $form_state);

    return $form;
  }

  /**
   * Handles switching the configuration type selector.
   */
  public function updateSelectedPluginType($form, FormStateInterface $form_state) {
    return $form['display']['settings'];
  }

  /**
   * Handles submit call when sensor type is selected.
   */
  public function submitSelectPlugin(array $form, FormStateInterface $form_state) {
    $this->entity = $this->buildEntity($form, $form_state);

    $form_state['rebuild'] = TRUE;
    // @todo: This is necessary because there are two different instances of the
    //   form object. Core should handle this.
    $form_state['build_info']['callback_object'] = $form_state['controller'];
  }

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::validate().
   */
  public function validate(array $form, FormStateInterface $form_state) {
    parent::validate($form, $form_state);

    $form_state['values']['label'] = trim($form_state['values']['label']);
    $form_values = $form_state['values'];

    //@todo Move this to the validation method for the confirm form plugin
    /*
    if ($form_values['link_type'] == 'confirm') {
      if (empty($form_values['flag_confirmation'])) {
        $this->setFormError('flag_confirmation', $form_state, $this->t('A flag confirmation message is required when using the confirmation link type.'));
      }
      if (empty($form_values['unflag_confirmation'])) {
        $this->setFormError('unflag_confirmation', $form_state, $this->t('An unflag confirmation message is required when using the confirmation link type.'));
      }
    }*/
    /*
        if (!preg_match('/^[a-z_][a-z0-9_]*$/', $form_values['id'])) {
          form_set_error('label', t('The flag name may only contain lowercase letters, underscores, and numbers.'));
        }
    */
  }

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::save().
   */
  public function save(array $form, FormStateInterface $form_state) {
    $flag = $this->entity;

    $flag->getFlagTypePlugin()->submitConfigurationForm($form, $form_state);
    $flag->getLinkTypePlugin()->submitConfigurationForm($form, $form_state);

    $flag->enable();
    $status = $flag->save();
    $url = $flag->url();
    if ($status == SAVED_UPDATED) {
      drupal_set_message(t('Flag %label has been updated.', array('%label' => $flag->label())));
      watchdog('flag', 'Flag %label has been updated.', array('%label' => $flag->label()), WATCHDOG_NOTICE, l(t('Edit'), $url . '/edit'));
    }
    else {
      drupal_set_message(t('Flag %label has been added.', array('%label' => $flag->label())));
      watchdog('flag', 'Flag %label has been added.', array('%label' => $flag->label()), WATCHDOG_NOTICE, l(t('Edit'), $url . '/edit'));
    }

    // We clear caches more vigorously if the flag was new.
    // _flag_clear_cache($flag->entity_type, !empty($flag->is_new));

    // Save permissions.
    // This needs to be done after the flag cache has been cleared, so that
    // the new permissions are picked up by hook_permission().
    // This may need to move to the flag class when we implement extra permissions
    // for different flag types: http://drupal.org/node/879988

    // If the flag machine name as changed, clean up all the obsolete permissions.
    if ($flag->id != $form['#flag_name']) {
      $old_name = $form['#flag_name'];
      $permissions = array("flag $old_name", "unflag $old_name");
      foreach (array_keys(user_roles()) as $rid) {
        user_role_revoke_permissions($rid, $permissions);
      }
    }
    /*
        foreach (array_keys(user_roles(!\Drupal::moduleHandler()->moduleExists('session_api'))) as $rid) {
          // Create an array of permissions, based on the checkboxes element name.
          $permissions = array(
            "flag $flag->name" => $flag->roles['flag'][$rid],
            "unflag $flag->name" => $flag->roles['unflag'][$rid],
          );
          user_role_change_permissions($rid, $permissions);
        }
    */
    // @todo: when we add database caching for flags we'll have to clear the
    // cache again here.

    $form_state['redirect'] = 'admin/structure/flags';
  }

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::delete().
   */
  public function delete(array $form, FormStateInterface $form_state) {
    $form_state['redirect'] = 'admin/structure/flags';
  }

}
