<?php
/**
 * Implementation of hook_hook_info().
 */
function flag_hook_info() {
  return array(
    'flag' => array(
      'flag' => array(
        'flag' => array(
          'runs when' => t('Content has been flagged'),
        ),
        'unflag' => array(
          'runs when' => t('Content has been unflagged')
        ),
      ),
    ),
  );
}

/**
 * Implementation of hook_action_info().
 */
function flag_action_info() {
  $actions = array(
    'flag_action_email' => array(
      'type' => 'flag',
      'description' => t('Send e-mail after flagging threshold'),
      'configurable' => TRUE,
      'hooks' => array(
        'flag' => array('flag'),
      ),
    ),
    'flag_action_unpublish' => array(
      'type' => 'flag',
      'description' => t('Unpublish after flagging threshold'),
      'configurable' => TRUE,
      'hooks' => array(
        'flag' => array('flag'),
      ),
    ),
    'flag_action_delete' => array(
      'type' => 'flag',
      'description' => t('Delete after flagging threshold'),
      'configurable' => TRUE,
      'hooks' => array(
        'flag' => array('flag', 'unflag'),
      ),
    ),
  );

  if (module_exists('modr8')) {
    $actions += array(
      'flag_action_moderate' => array(
        'type' => 'node',
        'description' => t('Moderate after flagging threshold'),
        'configurable' => TRUE,
        'hooks' => array(
          'flag' => array('flag', 'unflag'),
        ),
      ),
    );
  }

  return $actions;
}

/**
 * Menu callback for admin/build/flags/actions.
 */
function flag_actions_page() {
  include_once(drupal_get_path('module', 'trigger') .'/trigger.admin.inc');

  $result = db_query("SELECT aid FROM {actions} WHERE type = 'flag'");

  $enabled_actions = _trigger_get_hook_actions('flag', 'flag');

  $actions = array();
  while ($row = db_fetch_object($result)) {
    $action = actions_load($row->aid);
    $action->parameters = unserialize($action->parameters);
    $action->enabled = array_key_exists($row->aid, $enabled_actions);
    $actions[] = $action;
  }

  $add_action_form = drupal_get_form('flag_action_add_form');

  return theme('flag_actions_page', $actions, $add_action_form);
}

/**
 * Theme the list of actions currently in place for flags.
 */
function theme_flag_actions_page($actions, $add_action_form) {
  $rows = array();
  foreach ($actions as $action) {
    $row = array();
    $row[] = $action->description;
    $row[] = $action->parameters['threshold'];
    $row[] = implode(', ', array_filter($action->parameters['flags']));
    $row[] = $action->enabled ? t('enabled') : t('disabled');
    $row[] = l(t('edit'), 'admin/settings/actions/configure/'. $action->aid, array('query' => drupal_get_destination()));
    $row[] = l(t('delete'), 'admin/settings/actions/delete/'. $action->aid, array('query' => drupal_get_destination()));
    $rows[] = $row;
  }

  if (empty($rows)) {
    $rows[] = array(array('data' => t('Currently no flag actions. Use the <em>Add new flag action</em> form to add an action.'), 'colspan' => 6));
  }

  $header = array(
    t('Description'),
    t('Threshold'),
    t('Flags'),
    t('Enabled'),
    array('data' => t('Operations'), 'colspan' => 2),
  );

  $output = '';
  $output .= theme('table', $header, $rows);
  $output .= $add_action_form;
  return $output;
}

/**
 * Check if a flag action should be executed.
 */
function flag_action_check($context) {
  // Flagging operations only occur on the 'flag' operation.
  if ($context['action'] != 'flag') {
    return FALSE;
  }

  // Check if this action applies to this flag.
  if (in_array($context['flag'], $context['flags'])) {
    // Check if the flag is over the threshold.
    $counts = flag_get_counts($context['content_type'], $context['content_id']);
    if ($counts[$context['flag']] == $context['threshold']) {
      return TRUE;
    }
  }
  return FALSE;
}

/**
 * Modified version of the Add action form that redirects back to the flag list.
 */
function flag_action_add_form($form_state) {
  $all_actions = actions_list();
  $options = array(t('Choose an advanced action'));
  foreach ($all_actions as $aid => $action) {
    if ($action['type'] == 'flag') {
      $options[md5($aid)] = $action['description'] .'...';
    }
  }
  $form = system_actions_manage_form($form_state, $options);
  $form['parent']['#title'] = t('Add new flag action');
  return $form;
}

function flag_action_add_form_submit($form, &$form_state) {
  if ($form_state['values']['action']) {
    $form_state['redirect'] = array('admin/settings/actions/configure/'. $form_state['values']['action'], drupal_get_destination());
  }
}

/**
 * Generic configuration form for configuration of flag actions.
 */
function flag_action_form($context) {
  $flags = flag_get_flags();

  $options = array();
  foreach ($flags as $flag) {
    $options[$flag->name] = $flag->title;
  }
  $form['flags'] = array(
    '#type' => 'checkboxes',
    '#title' => t('Flags'),
    '#options' => $options,
    '#required' => TRUE,
    '#default_value' => isset($context['flags']) ? $context['flags'] : array(),
    '#weight' => -10,
  );
  $form['threshold'] = array(
    '#type' => 'textfield',
    '#title' => t('Flagging threshold'),
    '#size' => 6,
    '#maxlength' => 6,
    '#default_value' => isset($context['threshold']) ? $context['threshold'] : 10,
    '#required' => TRUE,
    '#weight' => -9,
  );

  $form['advanced'] = array(
    '#type' => 'fieldset',
    '#title' => t('Advanced'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
    '#tree' => FALSE,
    '#weight' => -1,
  );

  $form['advanced']['actions_description'] = array(
    '#type' => 'textfield',
    '#title' => t('Action description'),
    '#default_value' => $context['actions_description'],
    '#maxlength' => '255',
    '#description' => t('A unique description for this action.'),
    '#weight' => 1,
  );

  $form['advanced']['add_flag_trigger'] = array(
    '#type' => 'checkbox',
    '#title' => t('Include this action in the "Flag" trigger'),
    '#default_value' => isset($context['add_flag_trigger']) ? $context['add_flag_trigger'] : 1,
    '#description' => t('This checkbox can be used to easily add or remove this action from the "Flag" trigger.'),
    '#weight' => 1,
  );
  $form['actions_description'] = array();

  $form['#submit'] = array('system_actions_configure_submit', 'flag_action_form_submit');

  return $form;
}

/**
 * Generic submit handler for saving flag actions.
 */
function flag_action_form_submit($form, &$form_state) {
  include_once(drupal_get_path('module', 'trigger') .'/trigger.admin.inc');

  // TODO: Find a better way of finding the last added action.
  if (!isset($form_state['values']['actions_aid'])) {
    $form_state['values']['actions_aid'] = db_result(db_query("SELECT MAX(aid) as aid FROM {actions_aid}"));
  }

  // Build a set of values to pass to the trigger assign/unassign forms.
  $trigger_values = array(
    'operation' => 'flag',
    'hook' => 'flag',
    'confirm' => 1,
    'aid' => md5($form_state['values']['actions_aid']), // Wow.
  );

  // Check the status of the trigger before adding or removing an action.
  $aids = _trigger_get_hook_aids('flag', 'flag');
  if ($form_state['values']['add_flag_trigger'] && (!isset($form_values['actions_aid']) || !array_key_exists($form_values['actions_aid'], $aids))) {
    $trigger_form_state = array('values' => $trigger_values);
    trigger_assign_form_submit($form, $trigger_form_state);
  }
  elseif (!$form_state['values']['add_flag_trigger'] && array_key_exists($form_state['values']['actions_aid'], $aids)) {
    $trigger_form_state = array('values' => $trigger_values);
    trigger_unassign_submit($form, $trigger_form_state);
  }
}

function flag_action_email_form($context) {
  $form = flag_action_form($context);

  $form['flags']['#title'] = t('Send when any of the following flags are over the threshold');
  $form['flags']['#field_prefix'] = t('Send an e-mail after content has been flagged');
  $form['flags']['#field_suffix'] = t('times');

  $form['recipients'] = array(
    '#type' => 'textfield',
    '#title' => t('Recipients'),
    '#default_value' => isset($context['recipients']) ? $context['recipients'] : '[site-mail]',
    '#description' => t('Comma sepereated list of email addresses to which the message should be sent.'),
    '#weight' => -7,
  );
  $form['subject'] = array(
    '#type' => 'textfield',
    '#title' => t('Subject'),
    '#default_value' => isset($context['subject']) ? $context['subject'] : '[site-name] Content Flagged [flag-title]',
    '#description' => t('The subject of the message.'),
    '#weight' => -6,
  );
  $form['message'] = array(
    '#type' => 'textarea',
    '#title' => t('Message'),
    '#default_value' => isset($context['message']) ? $context['message'] : t("The [flag-action-content-type] [flag-action-title] has been flagged [flag-action-count] times with the [flag-title] flag.\n\nView this [flag-action-content-type] at [flag-action-content-url]."),
    '#description' => t('The message that should be sent.'),
    '#weight' => -5,
  );
  $form['tokens'] = array(
    '#type' => 'fieldset',
    '#title' => t('Tokens'),
    '#description' => t('The following tokens can be used in the recipients, subject, or message.'),
    '#value' => flag_token_help(array('flag','flag-action')),
    '#collapsible' => 1,
    '#collapsed' => 1,
    '#access' => module_exists('token'),
    '#weight' => -4,
  );
  return $form; 
}

function flag_action_email_validate($form, &$form_state) {
  // Validate e-mail addresses.
  $errors = array();
  $email_tokens = array('[site-mail]');
  $recipients = explode(',', $form_state['values']['recipients']);

  // Validate each recipient.
  foreach($recipients as $recipient) {
    $recipient = trim($recipient);
    if (!valid_email_address($recipient) && !in_array($recipient, $email_tokens)) {
      $errors['recipient'] = t('Please enter a valid email address or email token.');
    }
  }
  foreach ($errors as $name => $message) {
    form_set_error($name, $message);
  }
  return count($errors) == 0;
}

function flag_action_email_submit($form, &$form_state) {
  return array(
    'flags' => $form_state['values']['flags'],
    'threshold' => $form_state['values']['threshold'],
    'add_flag_trigger' => $form_state['values']['add_flag_trigger'],
    'recipients' => $form_state['values']['recipients'],
    'subject' => $form_state['values']['subject'],
    'message' => $form_state['values']['message'],
  );
}

/**
 * Action to send email upon flagging.
 */
function flag_action_email($flag, $context) {
  // Check if this action needs to be executed.
  if (!flag_action_check($context)) {
    return;
  }

  $language = language_default();
  $subject = $context['subject'];
  $message = $context['message'];
  $recipients = $context['recipients'];
  $flag_action = (object)$context;

  if (module_exists('token')) {
    global $user;
    $recipients = token_replace_multiple($recipients, array('flag' => $flag, 'flag-action' => $flag_action, 'user' => $user));
    $subject = token_replace_multiple($subject, array('flag' => $flag, 'flag-action' => $flag_action, 'user' => $user));
    $message = token_replace_multiple($message, array('flag' => $flag, 'flag-action' => $flag_action, 'user' => $user));
  }

  // Remove empty recipients.
  $recipients = implode(', ', array_filter(explode(',', $recipients)));

  // Ensure there are no new-lines in the subject.
  $subject = str_replace(array("\r", "\n"), '', $subject);

  if (drupal_mail('flag', 'over_threshold', $recipients, $language, array('body' => $message, 'subject' => $subject, 'flag_action' => $flag_action))) {
    watchdog('action', t('Sent email to %recipient', array('%recipient' => $recipients)));
  }
  else {
    watchdog('error', t('Unable to send email to %recipient', array('%recipient' => $recipients)));
  }
}

function flag_action_unpublish_form($context) {
  $form = flag_action_form($context);

  $form['flags']['#title'] = t('Unpublish content when any of the following flags are over the threshold');
  $form['threshold']['#field_prefix'] = t('Unpublish content that has been flagged');
  $form['threshold']['#field_suffix'] = t('times');

  return $form;
}

function flag_action_unpublish_submit($form, &$form_state) {
  return array(
    'flags' => $form_state['values']['flags'],
    'threshold' => $form_state['values']['threshold'],
    'add_flag_trigger' => $form_state['values']['add_flag_trigger'],
  );
}

/**
 * Action to unpublish content after flagging.
 */
function flag_action_unpublish($flag, $context) {
  // Check if this action needs to be executed.
  if (!flag_action_check($context)) {
    return;
  }

  switch ($context['content_type']) {
    case 'node':
      $node = node_load($context['content_id']);
      $node->status = 0;
      node_save($node);
      break;
  }
}


function flag_action_delete_form($context) {
  $form = flag_action_form($context);

  $form['flags']['#title'] = t('Delete content when any of the following flags are over the threshold');
  $form['threshold']['#field_prefix'] = t('Delete content that has been flagged');
  $form['threshold']['#field_suffix'] = t('times');
  $form['threshold']['#default_value'] = isset($context['threshold']) ? $context['threshold'] : 20;

  return $form;
}

function flag_action_delete_submit($form, &$form_state) {
  return array(
    'flags' => $form_state['values']['flags'],
    'threshold' => $form_state['values']['threshold'],
    'add_flag_trigger' => $form_state['values']['add_flag_trigger'],
  );
}

/**
 * Action to delete content after flagging.
 */
function flag_action_delete($flag, $context) {
  // Check if this action needs to be executed.
  if (!flag_action_check($context)) {
    return;
  }

  switch ($context['content_type']) {
    case 'node':
      node_delete($context['content_id']);
      break;
  }
}