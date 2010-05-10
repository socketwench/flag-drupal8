<?php
// $Id$

/**
 * @file
 * Flag module tokens support.
 */

/**
 * Implements of hook_token_info().
 */
function flag_token_info() {
  $types = array();
  $tokens = array();

  // Flag tokens.
  $types['flag'] = array(
    'name' => t('Flags'),
    'description' => t('Tokens related to flag data.'),
    'needs-data' => 'flag',
  );
  $tokens['flag']['flag-name'] = array(
    'name' => t('Flag name'),
    'description' => t('The flag machine-readable name.'),
  );
  $tokens['flag']['flag-title'] = array(
    'name' => t('Flag title'),
    'description' => t('The human-readable flag title.'),
  );

  // Flage action tokens.
  $types['flag-action'] = array(
    'name' => t('Flag actions'),
    'description' => t('Tokens available in response to a flag action being executed by a user.'),
    'needs-data' => 'flag-action',
  );
  $tokens['flag-action']['action'] = array(
    'name' => t('Flag action'),
    'description' => t('The flagging action taking place, either "flag" or "unflag".'),
  );
  $tokens['flag-action']['content-url'] = array(
    'name' => t('Flag content URL'),
    'description' => t('The URL of the content being flagged.'),
  );
  $tokens['flag-action']['content-title'] = array(
    'name' => t('Flag content title'),
    'description' => t('The title of the content being flagged.'),
  );
  $tokens['flag-action']['content-type'] = array(
    'name' => t('Flag content type'),
    'description' => t('The type of content being flagged, such as <em>node</em> or <em>comment</em>.'),
  );
  $tokens['flag-action']['content-id'] = array(
    'name' => t('Flag content ID'),
    'description' => t('The ID of content being flagged, may be a nid or cid.'),
  );
  $tokens['flag-action']['count'] = array(
    'name' => t('Flag count'),
    'description' => t('The current count total for this flag.'),
  );

  // Add tokens for the flag count available at the node/comment/user level.
  foreach (flag_get_types() as $flag_type) {
    $flags = flag_get_flags($flag_type);
    foreach ($flags as $flag) {
      $tokens[$flag_type]['flag-'. str_replace('_', '-', $flag->name) .'-count'] = array(
        'name' => t('@flag flag count', array('@flag' => $flag->get_title())),
        'description' => t('Total flag count for flag @flag', array('@flag' => $flag->get_title())),
      );
    }
  }

  return array(
    'types' => $types,
    'tokens' => $tokens,
  );
}

/**
 * Implements hook_tokens().
 */
function flag_tokens($type, $tokens, array $data = array(), array $options = array()) {
  $replacements = array();
  $sanitize = !empty($options['sanitize']);

  if ($type == 'flag' && !empty($data['flag'])) {
    $flag = $data['flag'];
    foreach ($tokens as $name => $original) {
      switch ($name) {
        case 'name':
          $replacements[$original] = $sanitize ? check_plain($flag->name) : $flag->name;
          break;
        case 'title':
          $replacements[$original] = $sanitize ? check_plain($flag->get_title()) : $flag->get_title();
          break;
      }
    }
  }
  elseif ($type == 'flag-action' && !empty($data['flag-action'])) {
    $action = $data['flag-action'];
    foreach ($tokens as $name => $original) {
      switch ($name) {
        case 'action':
          $replacements[$original] = $action->action;
          break;
        case 'content-url':
          $replacements[$original] = $sanitize ? check_url($action->content_url) : $action->content_url;
          break;
        case 'content-title':
          $replacements[$original] = $sanitize ? check_plain($action->content_title) : $action->content_title;
          break;
        case 'content-type':
          $replacements[$original] =$action->content_type;
          break;
        case 'content-id':
          $replacements[$original] =$action->content_id;
          break;
        case 'count':
          $replacements[$original] =$action->count;
          break;
      }
    }
  }

  if (isset($data[$type]) && in_array($type, flag_get_types())) {
    $flags = flag_get_flags($type);
    $object = $data[$type];
    foreach ($flags as $flag) {
      foreach ($tokens as $name => $original) {
        $flag_token = 'flag-'. str_replace('_', '-', $flag->name) .'-count';
        if ($name == $flag_token) {
          $replacements[$original] = $flag->get_count($flag->get_content_id($object));
        }
      }
    }
  }
  return $replacements;
}

/**
 * This is a replacement for Token's theme_token_help().
 *
 * This is a duplicate of the Token's function, but with a small modification:
 * the $type parameter is now $types, allowing for listing tokens from several
 * categories.
 *
 * @todo Replace this with whatever UI is created in D7 for tokens.
 */
function theme_flag_token_help($variables) {
  $types = $variables['types'];
  $prefix = $variables['prefix'];
  $suffix = $variables['suffix'];

  $tokens = flag_token_get_list($types);

  $header = array(t('Token'), t('Replacement value'));
  $rows = array();
  foreach ($tokens as $key => $category) {
    $rows[] = array(array('data' => drupal_ucfirst($key) . ' ' . t('tokens'), 'class' => array('region'), 'colspan' => 2));
    foreach ($category as $token => $description) {
      $row = array();
      $row[] = $prefix . $key . ':' . $token . $suffix;
      $row[] = $description;
      $rows[] = $row;
    }
  }

  $output = theme('table', array('header' => $header, 'rows' => $rows, 'attributes' => array('class' => array('description'))));
  return $output;
}

/**
 * This is a replacement for Token's token_get_list().
 *
 * It is used only by theme_flag_token_help(), above.
 *
 * This is a duplicate of the Token's function, but with a small modification:
 * the $type parameter is now $types, allowing for listing tokens from several
 * categories.
 *
 * @todo Replace this with whatever UI is created in D7 for tokens.
 */
function flag_token_get_list($types = array('all')) {
  $return = array();
  foreach ($types as $type) {
    foreach (module_implements('token_info') as $module) {
      $function = $module . '_token_info';
      $result = $function($type);
      if (is_array($result)) {
        foreach ($result['tokens'] as $category => $tokens) {
          foreach ($tokens as $token => $info) {
            $return[$category][$token] = $info['name'];
          }
        }
      }
    }
  }
  // For aesthetic reasons, we don't want the 'global' section to appear in
  // varying places, so let's move it to the bottom.
  if (isset($return['global'])) {
    $global = $return['global'];
    unset($return['global']);
    $return['global'] = $global;
  }
  return $return;
}
