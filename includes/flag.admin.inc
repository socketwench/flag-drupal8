<?php

/**
 * @file
 * Contains administrative pages for creating, editing, and deleting flags.
 */

use Drupal\flag\Handlers\AbstractFlag;
use Drupal\flag\Handlers\BrokenFlag;

/**
 * A form for ordering the weights of all the active flags in the system.
 */
function flag_admin_listing($form, &$form_state, $flags) {
  $form['#flags'] = $flags;
  $form['#tree'] = TRUE;

  foreach ($flags as $flag) {
    $form['flags'][$flag->name]['weight'] = array(
      '#type' => 'weight',
      '#delta' => count($flags) + 5,
      '#default_value' => $flag->weight,
      '#attributes' => array('class' => array('flag-weight')),
    );
  }

  $form['actions'] = array(
    '#type' => 'actions',
  );

  if (count($flags) == 1) {
    // Don't show weights with only one flag.
    unset($form['flags'][$flag->name]['weight']);
  }
  elseif (count($flags) > 1) {
    // Only show the form button if there are several flags.
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save flag order'),
    );
  }

  return $form;
}

/**
 * Submit handler for the flag_admin_listing form. Save flag weight ordering.
 */
function flag_admin_listing_submit($form, &$form_state) {
  foreach ($form['#flags'] as $flag) {
    if ($flag->weight != $form_state['values']['flags'][$flag->name]['weight']) {
      $flag->weight = $form_state['values']['flags'][$flag->name]['weight'];
      $flag->save();
    }
  }
}

/**
 * Theme the output of the normal, database flags into a table.
 */
function theme_flag_admin_listing($variables) {
  $form = $variables['form'];
  $flags = $form['#flags'];

  $output = '';

  foreach ($flags as $flag) {
    $ops = array(
      'flags_edit' =>  array('title' => t('edit'), 'href' => $flag->admin_path('edit')),
      'flags_fields' =>  array('title' => t('manage fields'), 'href' => $flag->admin_path('fields')),
      'flags_delete' =>  array('title' => t('delete'), 'href' => $flag->admin_path('delete')),
      'flags_export' =>  array('title' => t('export'), 'href' => $flag->admin_path('export')),
    );
    if (!module_exists('field_ui')) {
      unset($ops['flags_fields']);
    }
    $permission = "flag $flag->name";
    $roles = user_roles(FALSE, $permission);
    $row = array();
    $row[] = check_plain($flag->title) . ' <small>(' . t('Machine name: @name', array('@name' => $flag->name)) . ')</small>';
    if (count($flags) > 1) {
      $row[] = drupal_render($form['flags'][$flag->name]['weight']);
    }
    $row[] = $flag->entity_type;
    $row[] = empty($roles) ? '<em>' . t('No roles') . '</em>' : implode(', ', $roles);
    $row[] = $flag->types ? implode(', ', $flag->types) : '-';
    $row[] = $flag->global ? t('Yes') : t('No');
    $row[] = theme('links', array('links' => $ops));

    $rows[] = array(
      'data' => $row,
      'class' => array('draggable'),
    );
  }
  if (!$flags) {
    $rows[] = array(
      array('data' => t('No flags are currently defined.'), 'colspan' => 7),
    );
  }
  elseif (count($flags) > 1) {
    drupal_add_tabledrag('flag-admin-listing-table', 'order', 'sibling', 'flag-weight');
  }

  $header = array(t('Flag'));
  if (count($flags) > 1) {
    $header[] = t('Weight');
  }
  $header = array_merge($header, array(t('Flag type'), t('Roles'), t('Entity bundles'), t('Global?'), t('Operations')));
  $output .= theme('table', array(
    'header' => $header,
    'rows' => $rows,
    'attributes' => array('id' => 'flag-admin-listing-table'),
  ));
  $output .= drupal_render_children($form);

  return $output;
}

/**
 * Theme the list of disabled flags into a table.
 */
function theme_flag_admin_listing_disabled($variables) {
  $flags = $variables['flags'];
  $default_flags = $variables['default_flags'];
  $output = '';

  // Build a list of disabled, module-based flags.
  $rows = array();
  foreach ($default_flags as $name => $flag) {
    if (!isset($flags[$name])) {
      $ops = array();
      if (!$flag->is_compatible()) {
        $flag_updates_needed = TRUE;
        $ops['flags_update'] = array('title' => '<strong>' . t('update code') . '</strong>', 'href' => $flag->admin_path('update'), 'html' => TRUE);
      }
      else {
        $ops['flags_enable'] = array('title' => t('enable'), 'href' => $flag->admin_path('edit'));
      }
      // $flag->roles['flag'] not exist on older flags.
      $roles = array_flip(array_intersect(array_flip(user_roles()), !empty($flag->roles['flag']) ? $flag->roles['flag'] : array()));
      $rows[] = array(
        $flag->name,
        $flag->module,
        $flag->entity_type ? $flag->entity_type : t('Unknown'),
        theme('links', array('links' => $ops)),
      );
    }
  }

  if (isset($flag_updates_needed)) {
    drupal_set_message(t('Some flags provided by modules need to be updated to a new format before they can be used with this version of Flag. See the disabled flags for a list of flags that need updating.'), 'warning');
  }

  if (!empty($rows)) {
    $header = array(t('Disabled Flags'), t('Module'), t('Flag type'), t('Operations'));
    $output .= theme('table', array('header' => $header, 'rows' => $rows));
  }

  return $output;
}

/**
 * FormAPI after_build function set states on link type options fieldsets.
 *
 * We do this in an after build so we handle further link types fieldsets from
 * other modules that provide link types.
 *
 * This expects a link type's fieldset to be $form['display'][link_options_TYPE]
 * so that can be matched up with the radio button value.
 */
function flag_link_type_options_states($element) {
  $intro_element_values_array = array();
  foreach (element_children($element) as $key) {
    if (isset($element[$key]['#type']) && $element[$key]['#type'] == 'fieldset' && substr($key, 0, 12) == 'link_options') {
      // Trim the radio value from the fieldset key. This assumed the fieldset
      // key is 'link_options_TYPE'.
      $radio_value = substr($key, 13);
      $element[$key]['#states'] = array(
        'visible' => array(
          ':input[name="link_type"]' => array('value' => $radio_value),
        ),
      );
      // Gather up the radio values for the format we need for a multiple
      // value state.
      $intro_element_values_array[] = array('value' => $radio_value);
    }
  }

  $element['link_options_intro']['#states'] = array(
    'visible' => array(
      ':input[name="link_type"]' => $intro_element_values_array,
    ),
  );

  return $element;
}

/**
 * Form process handler for locking flag properties.
 *
 * Flags defined in code may define an array of properties in $flag->locked that
 * are to be locked and may not be edited by the user.
 */
function flag_form_locked_process($element, &$form_state, $form) {
  $flag = $form['#flag'];

  // Disable access to a form element whose name matches a locked flag property.
  if (isset($element['#name']) && !empty($flag->locked[$element['#name']])) {
    $element['#access'] = FALSE;
  }

  // Recurse into the form array.
  foreach (element_children($element) as $key) {
    // Workaround for Core inconvenience: setting #process here prevents an
    // element's essential #process handlers from its hook_element_info()
    // definition from being set in form_builder().
    // @see http://drupal.org/node/1779496
    if (isset($element[$key]['#type']) && ($info = element_info($element[$key]['#type']))) {
      if (isset($info['#process'])) {
        $element[$key]['#process'] = $info['#process'];
      }
    }

    $element[$key]['#process'][] = 'flag_form_locked_process';
  }

  return $element;
}

/**
 * Output the access options for roles in a table.
 */
function theme_flag_form_roles($variables) {
  $element = $variables['element'];

  $header = array(
    array('class' => array('checkbox'), 'data' => t('Flag')),
    array('class' => array('checkbox'), 'data' => t('Unflag')),
    t('Role'),
  );
  $rows = array();
  foreach (element_children($element['flag']) as $role) {
    $row = array();
    $role_name = $element['flag'][$role]['#title'];
    unset($element['flag'][$role]['#title']);
    unset($element['unflag'][$role]['#title']);
    $element['flag'][$role]['#attributes']['class'] = array('flag-access');
    $element['unflag'][$role]['#attributes']['class'] = array('unflag-access');
    $row[] = array('class' => array('checkbox'), 'data' => drupal_render($element['flag'][$role]));
    $row[] = array('class' => array('checkbox'), 'data' => drupal_render($element['unflag'][$role]));
    $row[] = $role_name;
    $rows[] = $row;
  }

  return theme('table', array(
    'header' => $header,
    'rows' => $rows,
    'attributes' => array(
      'class' => array('flag-admin-table'),
      'id' => 'flag-roles',
    ),
  ));
}

/**
 * Delete flag page.
 */
function flag_delete_confirm($form, &$form_state, $flag) {
  $form['#flag'] = $flag;

  return confirm_form($form,
    t('Are you sure you want to delete %title?', array('%title' => $flag->get_title())),
    !empty($_GET['destination']) ? $_GET['destination'] : FLAG_ADMIN_PATH,
    isset($flag->module) ? t('This flag is provided by the %module module. It will lose any customizations and be disabled.', array('%module' => $flag->module)) : t('This action cannot be undone.'),
    t('Delete'), t('Cancel')
  );
}

function flag_delete_confirm_submit($form, &$form_state) {
  $flag = $form['#flag'];
  if ($form_state['values']['confirm']) {
    $flag->delete();
    $flag->disable();
    _flag_clear_cache($flag->entity_type, TRUE);
  }
  drupal_set_message(t('Flag @name has been deleted.', array('@name' => $flag->get_title())));
  $form_state['redirect'] = FLAG_ADMIN_PATH;
}

/**
 * FormAPI after_build function to check that the link type exists.
 */
function flag_check_link_types($element) {
  $link_types = flag_get_link_types();
  if (!isset($link_types[$element['#value']])) {
    drupal_set_message(t('This flag uses a link type of %type, which does not exist.', array('%type' => $element['#value'])), 'error');
  }
  return $element;
}

/**
 * Clears various caches when one or more flags are modified.
 *
 * @param $entity_types
 *  The entity types for the flags. May be a single value or an array.
 * @param $is_insert_or_delete
 *  Whether the modified flag is being inserted (saved for the first time) or
 *  deleted. This results in a more vigorous clearing of caches. In
 *  particular, when no flags exist yet, no Field admin UI paths exist and these
 *  need to be created.
 */
function _flag_clear_cache($entity_types, $is_insert_or_delete = FALSE) {
  if (!is_array($entity_types)) {
    $entity_types = array($entity_types);
  }

  // Reset our flags cache, thereby making the following code aware of the
  // modifications.
  drupal_static_reset('flag_get_flags');

  if ($is_insert_or_delete) {
    // A new or deleted flag means we are changing bundles on the Flagging
    // entity, and thus need to clear the entity info cache.
    entity_info_cache_clear();
  }

  // Clear FieldAPI's field_extra cache, so our changes to pseudofields are
  // noticed. It's rather too much effort to both a) check whether the
  // pseudofield setting has changed either way, and b) specifically clear just
  // the bundles that are (or were!!) affected, so we just clear for all bundles
  // on our entity type regardlesss.
  foreach ($entity_types as $entity_type) {
    cache_clear_all("field_info:bundle_extra:$entity_type:", 'cache_field', TRUE);
  }

  if (module_exists('views')) {
    views_invalidate_cache();
  }

  // The title of a flag may appear in the menu (indirectly, via our "default
  // views"), so we need to clear the menu cache. This call also clears the
  // page cache, which is desirable too because the flag labels may have
  // changed.
  menu_rebuild();
}
