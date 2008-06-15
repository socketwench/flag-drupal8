<?php

/**
 * Implementation of hook_token().
 */

function flag_token_list($type = 'all') {
  $tokens = array();
  if ($type == 'flag' || $type == 'all') {
    $tokens['flag']['flag-name'] = t('The flag name.');
    $tokens['flag']['flag-title'] = t('The flag title.');
  }
  if ($type == 'flag-action' || $type == 'all') {
    $tokens['flag-action']['flag-action'] = t('The flagging action taking place.');
    $tokens['flag-action']['flag-content-url'] = t('The URL of the content being flagged.');
    $tokens['flag-action']['flag-content-title'] = t('The title of the content being flagged.');
    $tokens['flag-action']['flag-content-type'] = t('The type of content being flagged, such as <em>node</em> or <em>comment</em>.');
    $tokens['flag-action']['flag-content-id'] = t('The ID of content being flagged, may be a nid or cid.');
    $tokens['flag-action']['flag-count'] = t('The current count total for this flag.');
  }
  if ($type == 'node' || $type == 'all') {
    $flags = flag_get_flags();
    foreach ($flags as $flag) {
      $tokens['node']['flag-'. str_replace('_', '-', $flag->name) .'-count'] = t('Total flag count for flag @flag', array('@flag' => $flag->title));
    }
  }
  return $tokens;
}

/**
 * Implementation of hook_token_values().
 */
function flag_token_values($type, $object = NULL) {
  $values = array();
  if ($type == 'flag') {
    $values['flag-name'] = check_plain($object->name);
    $values['flag-title'] = check_plain($object->title);
  }
  elseif ($type == 'flag-action') {
    $counts = flag_get_counts($object->content_type, $object->content_id);
    $values['flag-action'] = $object->action;
    $values['flag-content-url'] = $object->content_url;
    $values['flag-content-title'] = $object->content_title;
    $values['flag-content-type'] = $object->content_type;
    $values['flag-content-id'] = $object->content_id;
    $values['flag-count'] = $counts[$object->flag];
  }
  elseif ($type == 'node') {
    $values = array();
    $counts = flag_get_counts('node', $node->nid);
    $flags = flag_get_flags();
    foreach ($flags as $flag) {
      $count = isset($counts[$flag->name]) ? $counts[$flag->name] : 0;
      $values['flag-'. str_replace('_', '-', $flag->name) .'-count'] = $count;
    }
  }

  return $values;
}

/**
 * Retreive a list of tokens from multiple types. Merged into a single array.
 *
 * @param $types
 *   An array of all types that should return their tokens in a single array.
 * @return
 *   The array of usable tokens and their descriptions, organized by
 *   token type.
 */
function flag_token_get_list($types = array('all')) {
  token_include();
  $return = array();
  foreach($types as $type) {
    $return = array_merge_recursive($return, module_invoke_all('token_list', $type));
  }
  return $return;
}

/**
 * Generate token help text for form elements in nodeflag.
 */
function flag_token_help($types = array('user','node','flag')) {
  return theme('flag_token_help', $types);
}

/**
 * For a given context, builds a formatted list of tokens and descriptions
 * of their replacement values.
 *
 * @param $types
 *   The token types to display documentation for. Defaults to 'all'.
 * @param $prefix
 *   The prefix your module will use when parsing tokens. Defaults to '['.
 * @param $suffix
 *   The suffix your module will use when parsing tokens. Defaults to ']'.
 * @return
 *   An HTML table containing the formatting docs.
 **/
function theme_flag_token_help($types = array('all'), $prefix = '[', $suffix = ']') {
  token_include();
  $tokens = flag_token_get_list($types);

  $headers = array(t('Token'), t('Replacement value'));
  $rows = array();
  foreach ($tokens as $key => $category) {
    $rows[] = array(array('data' => drupal_ucfirst($key) . ' ' . t('tokens'), 'class' => 'region', 'colspan' => 2));
    foreach ($category as $token => $description) {
      $row = array();
      $row[] = $prefix . $token . $suffix;
      $row[] = $description;
      $rows[] = $row;
    }
  }

  $output = theme('table', $headers, $rows, array('class' => 'description'));
  return $output;
}
