<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 9/28/13
 * Time: 10:23 PM
 */

namespace Drupal\flag\Form;

use Drupal\Core\Form\FormBase;
use Drupal\flag\Handlers\AbstractFlag;

class FlagAddForm extends FormBase{

  public function getFormID() {
    return 'flag_add';
  }

  public function buildForm(array $form, array &$form_state, $entity_type = NULL) {
    $flag = \Drupal\flag\Handlers\AbstractFlag::factory_by_entity_type($entity_type);
    // Mark the flag as new.
    $flag->is_new = TRUE;
    $type_info = flag_fetch_definition($entity_type);
    drupal_set_title(t('Add new @type flag', array('@type' => $type_info['title'])));


    $form['#flag'] = $flag;
    $form['#flag_name'] = $flag->name;

    $form['title'] = array(
      '#type' => 'textfield',
      '#title' => t('Title'),
      '#default_value' => $flag->title,
      '#description' => t('A short, descriptive title for this flag. It will be used in administrative interfaces to refer to this flag, and in page titles and menu items of some <a href="@insite-views-url">views</a> this module provides (theses are customizable, though). Some examples could be <em>Bookmarks</em>, <em>Favorites</em>, or <em>Offensive</em>.', array('@insite-views-url' => url('admin/structure/views'))),
      '#maxlength' => 255,
      '#required' => TRUE,
      '#weight' => -3,
    );

    $form['name'] = array(
      '#type' => 'machine_name',
      '#title' => t('Machine name'),
      '#default_value' => $flag->name,
      '#description' => t('The machine-name for this flag. It may be up to 32 characters long and may only contain lowercase letters, underscores, and numbers. It will be used in URLs and in all API calls.'),
      '#maxlength' => 32,
      '#weight' => -2,
      '#machine_name' => array(
        'exists' => 'flag_get_flag',
        'source' => array('title'),
      ),
    );

    $form['global'] = array(
      '#type' => 'checkbox',
      '#title' => t('Global flag'),
      '#default_value' => $flag->global,
      '#description' => t('If checked, flag is considered "global" and each entity is either flagged or not. If unchecked, each user has individual flags on entities.'),
      '#weight' => -1,
    );
    // Don't allow the 'global' checkbox to be changed when flaggings exist:
    // there are too many unpleasant consequences in either direction.
    // @todo: Allow this, but with a confirmation form, assuming anyone actually
    // needs this feature.
    if (!empty($flag->fid) && flag_get_flag_counts($flag->name)) {
      $form['global']['#disabled'] = TRUE;
      $form['global']['#description'] .= '<br />' . t('This setting cannot be changed when flaggings exist for this flag.');
    }

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

    $form['messages']['tokens_help'] = array(
      '#title' => t('Token replacement'),
      '#type' => 'fieldset',
      '#description' =>
      '<p>' . t('The above six texts may contain any of the tokens listed below. For example, <em>"Flag link text"</em> could be entered as:') . '</p>' .
      theme('item_list', array(
        'items' => array(
          t('Add &lt;em&gt;[node:title]&lt;/em&gt; to your favorites'),
          t('Add this [node:type] to your favorites'),
          t('Vote for this proposal ([node:flag-vote-count] people have already done so)'),
        ),
        'attributes' => array('class' => 'token-examples'),
      )) .
      '<p>' . t('These tokens will be replaced with the appropriate fields from the node (or user, or comment).') . '</p>' .
      theme('flag_tokens_browser', array('types' => $flag->get_labels_token_types())),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    );

    $form['access'] = array(
      '#type' => 'fieldset',
      '#title' => t('Flag access'),
      '#tree' => FALSE,
      '#weight' => 10,
    );

    // Flag classes will want to override this form element.
    $form['access']['types'] = array(
      '#type' => 'checkboxes',
      '#title' => t('Flaggable types'),
      '#options' => array(),
      '#default_value' => $flag->types,
      '#description' => t('Check any sub-types that this flag may be used on.'),
      '#required' => TRUE,
      '#weight' => 10,
    );

    // Disabled access breaks checkboxes unless #value is hard coded.
    if (!empty($flag->locked['types'])) {
      $form['access']['types']['#value'] = $flag->types;
    }

    // Load the user permissions into the flag.
    if (isset($flag->fid)) {
      $flag->fetch_roles();
    }
    elseif (isset($flag->import_roles)) {
      // Convert the roles data from old API 2 flags that have been run through
      // the update system.
      // @see FlagUpdate_2::update()
      $flag->roles = $flag->import_roles;
    }
    else {
      // For new flags, provide a reasonable default value.
      $flag->roles = array(
        'flag' => array(DRUPAL_AUTHENTICATED_RID),
        'unflag' => array(DRUPAL_AUTHENTICATED_RID),
      );
    }

    $form['access']['roles'] = array(
      '#title' => t('Roles that may use this flag'),
      '#description' => t('Users may only unflag content if they have access to flag the content initially. Checking <em>authenticated user</em> will allow access for all logged-in users.'),
      '#theme' => 'flag_form_roles',
      '#theme_wrappers' => array('form_element'),
      '#weight' => -2,
      '#attached' => array(
        'js' => array(drupal_get_path('module', 'flag') . '/theme/flag-admin.js'),
        'css' => array(drupal_get_path('module', 'flag') . '/theme/flag-admin.css'),
      ),
    );
    if (module_exists('session_api')) {
      $form['access']['roles']['#description'] .= ' ' . t('Support for anonymous users is being provided by <a href="http://drupal.org/project/session_api">Session API</a>.');
    }
    else {
      $form['access']['roles']['#description'] .= ' ' . t('Anonymous users may flag content if the <a href="http://drupal.org/project/session_api">Session API</a> module is installed.');
    }

    $form['access']['roles']['flag'] = array(
      '#type' => 'checkboxes',
      '#options' => user_roles(!module_exists('session_api')),
      '#default_value' => $flag->roles['flag'],
      '#parents' => array('roles', 'flag'),
    );
    $form['access']['roles']['unflag'] = array(
      '#type' => 'checkboxes',
      '#options' => user_roles(!module_exists('session_api')),
      '#default_value' => $flag->roles['unflag'],
      '#parents' => array('roles', 'unflag'),
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
      '#after_build' => array('flag_link_type_options_states'),
    );

    $form['display']['link_type'] = array(
      '#type' => 'radios',
      '#title' => t('Link type'),
      '#options' => _flag_link_type_options(),
      // @todo: Move flag_check_link_types into controller?
      '#after_build' => array('flag_check_link_types'),
      '#default_value' => $flag->link_type,
      // Give this a high weight so additions by the flag classes for entity-
      // specific options go above.
      '#weight' => 18,
      '#attached' => array(
        'js' => array(drupal_get_path('module', 'flag') . '/theme/flag-admin.js'),
      ),
      '#attributes' => array(
        'class' => array('flag-link-options'),
      ),
    );
    // Add the descriptions to each ratio button element. These attach to the
    // elements when FormAPI expands them.
    foreach (_flag_link_type_descriptions() as $key => $description) {
      $form['display']['link_type'][$key]['#description'] = $description;
    }

    $form['display']['link_options_intro'] = array(
      // This is a hack to allow a markup element to use FormAPI states.
      // @see http://www.bywombats.com/blog/06-25-2011/using-containers-states-enabled-markup-form-elements
      '#type' => 'container',
      '#children' => '<p id="link-options-intro">' . t('The selected link type may require these additional settings:') . '</p>',
      '#weight' => 20,
    );

    $form['display']['link_options_confirm'] = array(
      '#type' => 'fieldset',
      '#title' => t('Options for the "Confirmation form" link type'),
      // Any "link type" provider module must put its settings fields inside
      // a fieldset whose HTML ID is link-options-LINKTYPE, where LINKTYPE is
      // the machine-name of the link type. This is necessary for the
      // radiobutton's JavaScript dependency feature to work.
      '#id' => 'link-options-confirm',
      '#weight' => 21,
    );

    $form['display']['link_options_confirm']['flag_confirmation'] = array(
      '#type' => 'textfield',
      '#title' => t('Flag confirmation message'),
      '#default_value' => isset($flag->flag_confirmation) ? $flag->flag_confirmation : '',
      '#description' => t('Message displayed if the user has clicked the "flag this" link and confirmation is required. Usually presented in the form of a question such as, "Are you sure you want to flag this content?"'),
    );

    $form['display']['link_options_confirm']['unflag_confirmation'] = array(
      '#type' => 'textfield',
      '#title' => t('Unflag confirmation message'),
      '#default_value' => isset($flag->unflag_confirmation) ? $flag->unflag_confirmation : '',
      '#description' => t('Message displayed if the user has clicked the "unflag this" link and confirmation is required. Usually presented in the form of a question such as, "Are you sure you want to unflag this content?"'),
    );

    $form['actions'] = array(
      '#type' => 'actions',
    );

    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save flag'),
      // We put this button on the form before calling $flag->options_form()
      // to give the flag handler a chance to remove it (e.g. flag_broken).
      '#weight' => 999,
    );

    // Add our process handler to disable access to locked properties.
    //@todo: Fix reference to flag_form_locked_process, or replace entirely.
//    $form['#process'][] = 'flag_form_locked_process';

    // Allow the flag handler to make additions and changes to the form.
    // Note that the flag_broken handler will completely empty the form array!
    $flag->options_form($form);

    return $form;
  }

  public function validateForm(array &$form, array &$form_state) {
    $form_state['values']['title'] = trim($form_state['values']['title']);
    $form_values = $form_state['values'];

    if ($form_values['link_type'] == 'confirm') {
      if (empty($form_values['flag_confirmation'])) {
        form_set_error('flag_confirmation', t('A flag confirmation message is required when using the confirmation link type.'));
      }
      if (empty($form_values['unflag_confirmation'])) {
        form_set_error('unflag_confirmation', t('An unflag confirmation message is required when using the confirmation link type.'));
      }
    }


    $flag = $form['#flag'];
    $flag->form_input($form_values);
    $errors = $flag->validate();
    foreach ($errors as $field => $field_errors) {
      foreach ($field_errors as $error) {
        form_set_error($field, $error['message']);
      }
    }
  }

  public function submitForm(array &$form, array &$form_state) {
    $flag = $form['#flag'];

    $form_state['values']['title'] = trim($form_state['values']['title']);
    $flag->form_input($form_state['values']);

    $flag->save();
    $flag->enable();
    drupal_set_message(t('Flag @title has been saved.', array('@title' => $flag->get_title())));
    // We clear caches more vigorously if the flag was new.
    _flag_clear_cache($flag->entity_type, !empty($flag->is_new));

    // Save permissions.
    // This needs to be done after the flag cache has been cleared, so that
    // the new permissions are picked up by hook_permission().
    // This may need to move to the flag class when we implement extra permissions
    // for different flag types: http://drupal.org/node/879988

    // If the flag machine name as changed, clean up all the obsolete permissions.
    if ($flag->name != $form['#flag_name']) {
      $old_name = $form['#flag_name'];
      $permissions = array("flag $old_name", "unflag $old_name");
      foreach (array_keys(user_roles()) as $rid) {
        user_role_revoke_permissions($rid, $permissions);
      }
    }

    foreach (array_keys(user_roles(!module_exists('session_api'))) as $rid) {
      // Create an array of permissions, based on the checkboxes element name.
      $permissions = array(
        "flag $flag->name" => $flag->roles['flag'][$rid],
        "unflag $flag->name" => $flag->roles['unflag'][$rid],
      );
      user_role_change_permissions($rid, $permissions);
    }
    // @todo: when we add database caching for flags we'll have to clear the
    // cache again here.

    $form_state['redirect'] = FLAG_ADMIN_PATH;
  }
}