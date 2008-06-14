<?php
// $Id$

/**
 * @file
 * Provides support for the Views module.
 */

/**
 * Implementation of hook_views_date().
 */
function flag_views_data() {
  $data = array();

  $data['flag_content']['table']['group'] = t('Flags');
  $data['flag_counts']['table']['group'] = t('Flags');

  $data['flag_content']['uid'] = array(
    'title' => t('User ID'),
    'help' => t('The user that flagged an item.'),
    'relationship' => array(
      'base' => 'users',
      'field' => 'uid',
      'handler' => 'views_handler_relationship',
      'label' => t('Flag user'),
    ),
  );

  $data['flag_content']['timestamp'] = array(
    'title' => t('Flagged time'),
    'help' => t('Display the time the node was flagged by a user.'),
    'field' => array(
      'handler' => 'views_handler_field_date',
      'click sortable' => TRUE,
     ),
    'sort' => array(
      'handler' => 'views_handler_sort',
    ),
    'filter' => array(
      'handler' => 'views_handler_filter_date',
    ),
    'argument' => array(
      'handler' => 'views_handler_argument_date',
    ),
  );

  // Specialized is null/is not null filter.
  $data['flag_content']['flagged'] = array(
    'title' => t('Flagged'),
    'help' => t('Filter to ensure a node has or has not been flagged.'),
    'real field' => 'uid',
    'filter' => array(
      'handler' => 'flag_handler_filter_flagged',
      'label' => t('Flagged'),
    ),
  );

  // Flag node links.
  $data['flag_content']['ops'] = array(
    'title' => t('Flag link'),
    'help' => t('Display flag/unflag links.'),
    'real field' => 'uid',
    'field' => array(
      'handler' => 'flag_handler_field_ops',
    ),
  );

  // Flag count totals.
  $data['flag_counts']['count'] = array(
    'title' => t('Flag Count'),
    'help' => t('The number of times a node has been flagged total by any user.'),
    'field' => array(
      'handler' => 'views_handler_field_numeric',
      'click sortable' => TRUE,
     ),
    'sort' => array(
      'handler' => 'views_handler_sort',
    ),
    'filter' => array(
      'handler' => 'views_handler_filter_numeric',
    ),
    'argument' => array(
      'handler' => 'views_handler_argument_numeric',
    ),
  );

  return $data;
}

/**
 * Implementation of hook_views_data_alter().
 */
function flag_views_data_alter(&$data) {
  // Flag node relationship.
  $data['node']['flag_content_rel'] = array(
    'group' => t('Flags'),
    'title' => t('Flagged'),
    'help' => t('Limit results to a particular flag, or display information about the flags on a node.'),
    'real field' => 'nid',
    'relationship' => array(
      'handler' => 'flag_handler_relationship_content_node',
      'base' => 'flag_content',
      'field' => 'content_id',
      'label' => t('flag'),
    ),
  );

  // Flag-count node relationship.
  $data['node']['flag_counts_rel'] = array(
    'group' => t('Flags'),
    'title' => t('Flag counts'),
    'help' => t('Add information about the flag total counts.'),
    'real field' => 'nid',
    'relationship' => array(
      'handler' => 'flag_handler_relationship_counts_node',
      'base' => 'flag_counts',
      'field' => 'content_id',
      'label' => t('flag'),
    ),
  );
}

/**
 * A helper function that creates a radio list of available flags.
 *
 * This function is used to select the desired flag when setting up flag
 * relationships and fields.
 */
function flag_views_flag_config_form($type, $current_flag) {
  $flags = flag_get_flags();
  foreach ($flags as $flag) {
    // Localize Flag titles.
    $flag = flag_process_labels($flag, NULL, NULL, array('title'));
    $options[$flag->name] = $flag->title;
  }

  $form = array(
    '#type' => $type,
    '#title' => t('Flag'),
    '#options' => $options,
    '#default_value' => $current_flag,
    '#required' => TRUE,
  );

  return $form;
}

/**
 * Helper function that gets the first defined flag and returns it's name.
 */
function flag_views_flag_default() {
  static $default_flag;

  if (!isset($default_flag)) {
    $flag = array_shift(flag_get_flags());
    $default_flag = $flag->name;
  }

  return $default_flag;
}

/**
 * Specialized relationship handler associating flags and nodes.
 */
class flag_handler_relationship_content_node extends views_handler_relationship {
  function options(&$options) {
    parent::options($options);
    $options['flag'] = flag_views_flag_default();
    $options['user_scope'] = 'current';
    $options['required'] = 1;
  }

  function options_form(&$form, &$form_state) {
    parent::options_form($form, $form_state);
    $form['label']['#description'] .= ' '. t('The name of the selected flag makes a good label.');

    $form['flag'] = flag_views_flag_config_form('radios', $this->options['flag']);
    $form['flag']['#title'] = t('Flagged');

    $form['user_scope'] = array(
      '#type' => 'radios',
      '#title' => t('By'),
      '#options' => array('current' => t('Current user'), 'any' => t('Any user')),
      '#default_value' => $this->options['user_scope'],
    );

    $form['required']['#title'] = t('Include only flagged content');
    $form['required']['#description'] = t('If checked, only content that has this flag will be included. Leave unchecked if needing to display lists of specifically unflagged content.');
  }

  function admin_summary() {
    return $this->options['flag'] .' - '. ($this->options['user_scope'] == 'current' ? 'current user' : 'any user');
  }

  /**
   * Called to implement a relationship in a query.
   */
  function query() {
    // Figure out what base table this relationship brings to the party.
    $join = new views_join();
    $join->definition = array(
      'table' => 'flag_content',
      'field' => 'content_id',
      'left_table' => 'node',
      'left_field' => 'nid',
    );

    if (!empty($this->options['required'])) {
      $join->definition['type'] = 'INNER';
    }

    $flag = flag_get_flag($this->options['flag']);
    $join->definition['extra'] = array(
      array(
        'field' => 'fid',
        'value' => $flag->fid,
        'numeric' => TRUE,
      ),
      array(
        'field' => 'content_type',
        'alias' => $this->alias,
        'value' => 'node',
        'numeric' => FALSE,
      ),
    );
    if ($this->options['user_scope'] == 'current' && !$flag->global) {
      $join->definition['extra'][] = array(
        'field' => 'uid',
        'value' => '***CURRENT_USER***',
        'numeric' => TRUE,
      );
    }
    $join->construct();

    $alias = $join->definition['table'] . '_' . $join->definition['left_table'];

    $this->alias = $this->query->add_relationship($alias, $join, 'flag_content', $this->relationship);

    // Save the flag to the view so we can retrieve it when outputing.
    $this->view->flags[$this->alias] = $flag;
  }
}


/**
 * Specialized relationship handler associating flag counts and nodes.
 */
class flag_handler_relationship_counts_node extends views_handler_relationship {
  function options(&$options) {
    parent::options($options);
    $options['flag'] = flag_views_flag_default();
    $options['required'] = 1;
  }

  function options_form(&$form, &$form_state) {
    parent::options_form($form, $form_state);
    $form['flag'] = flag_views_flag_config_form('radios', $this->options['flag']);

    $form['required']['#title'] = t('Include only content flagged at least once');
    $form['required']['#description'] = t('If checked, only content that has been flagged will be included.');
  }

  function admin_summary() {
    return $this->options['flag'];
  }

  /**
   * Called to implement a relationship in a query.
   */
  function query() {
    // Figure out what base table this relationship brings to the party.
    $join = new views_join();
    $join->definition = array(
      'table' => 'flag_counts',
      'field' => 'content_id',
      'left_table' => 'node',
      'left_field' => 'nid',
    );

    if (!empty($this->options['required'])) {
      $join->definition['type'] = 'INNER';
    }

    $flag = flag_get_flag($this->options['flag']);
    $join->definition['extra'] = array(
      array(
        'field' => 'fid',
        'value' => $flag->fid,
        'numeric' => TRUE,
      ),
      array(
        'field' => 'content_type',
        'alias' => $this->alias,
        'value' => 'node',
        'numeric' => FALSE,
      ),
    );
    $join->construct();

    $alias = $join->definition['table'] . '_' . $join->definition['left_table'];

    $this->alias = $this->query->add_relationship($alias, $join, 'flag_counts', $this->relationship);

    // Save the flag to the view so we can retrieve it when outputing.
    $this->view->flags[$this->alias] = $flag;
  }
}

/**
 * Views field handler for the Flag operations links (flag/unflag).
 */
class flag_handler_field_ops extends views_handler_field {
  function init(&$view, &$options) {
    parent::init($view, $options);

    // We need the node type to check access in the render function.
    $this->additional_fields['node_type'] = array(
      'table' => 'node',
      'field' => 'type',
    );
  }

  function render($values) {
    // Pull up the flag object created by the relationship.
    $flag = $this->view->flags[$this->table_alias];
    $node_type = $values->{$this->aliases['node_type']};
    $is_flagged = !is_null($values->{$this->field_alias});

    if (!flag_access($flag) || !flag_content_enabled($flag, 'node', $node_type)) {
      // Flag does not apply to this user or node type.
      return;
    }

    // Token replacements.
    $flag = flag_process_labels($flag, 'node', $values->nid, array('flag_short', 'flag_long', 'flag_message'));
    return theme('flag', $flag, 'node', $values->nid, $is_flagged);
  }
}

/**
 * Handler to filter for nodes that are not in a queue.
 */
class flag_handler_filter_flagged extends views_handler_filter_boolean_operator {
  function options(&$options) {
    parent::options($options);
    $options['value'] = 1;
  }

  function options_form(&$form, &$form_state) {
    parent::options_form($form, $form_state);
    $form['value']['#type'] = 'radios';
    $form['value']['#title'] = t('Status');
    $form['value']['#options'] = array(1 => t('Flagged'), 0 => t('Not flagged'));
    $form['value']['#default_value'] = empty($this->options['value']) ? 0 : $this->options['value'];
    $form['value']['#description'] = t('This filter only works if the relationship used has the "Include only flagged content" option unchecked. Otherwise, this filter is not necessary, because all records are already limited to flagged content.');
  }

  function query() {
    $operator = $this->value ? 'IS NOT' : 'IS';
    $this->query->add_where($this->options['group'], $this->relationship .'.uid '. $operator .' NULL');
  }
}

/**
 * Implementation of hook_views_default_views()
 */
function flag_views_default_views() {
  $flags = flag_get_flags();

  foreach ($flags as $flag) {
    $flag = flag_process_labels($flag, NULL, NULL, array('title'));

    $view = new view;
    $view->name = 'flag_'. $flag->name;
    $view->description = t('View for flag: !flag-title', array('!flag-title' => $flag->title));
    $view->tag = t('default');
    $view->view_php = '';
    $view->base_table = 'node';
    $view->is_cacheable = '0';
    $view->api_version = 2;
    $view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */
    $handler = $view->new_display('default', 'Defaults', 'default');
    $handler->override_option('relationships', array(
      'uid' => array(
        'label' => 'user',
        'required' => 0,
        'id' => 'uid',
        'table' => 'flag_content',
        'field' => 'uid',
        'relationship' => 'flag_content_rel',
      ),
      'flag_content_rel' => array(
        'label' => $flag->name,
        'required' => 1,
        'flag' => $flag->name,
        'user_scope' => 'current',
        'id' => 'flag_content_rel',
        'table' => 'node',
        'field' => 'flag_content_rel',
        'relationship' => 'none',
      ),
    ));

    $fields = array(
      'type' => array(
        'id' => 'type',
        'table' => 'node',
        'field' => 'type',
        'label' => 'Type',
      ),
      'title' => array(
        'id' => 'title',
        'table' => 'node',
        'field' => 'title',
        'label' => 'Title',
      ),
      'name' => array(
        'label' => 'Author',
        'link_to_user' => 1,
        'id' => 'name',
        'table' => 'users',
        'field' => 'name',
        'relationship' => 'uid',
      ),
    );

    if (module_exists('comment')) {
      $fields += array(
        'comment_count' => array(
          'id' => 'comment_count',
          'table' => 'node_comment_statistics',
          'field' => 'comment_count',
          'label' => 'Replies',
        ),
        'last_comment_timestamp' => array(
          'id' => 'last_comment_timestamp',
          'table' => 'node_comment_statistics',
          'field' => 'last_comment_timestamp',
          'label' => 'Last Post',
        ),
      );
    }

    $fields += array(
      'ops' => array(
        'label' => 'Ops',
        'id' => 'ops',
        'table' => 'flag_content',
        'field' => 'ops',
        'relationship' => 'flag_content_rel',
      ),
    );

    $handler->override_option('fields', $fields);
    $handler->override_option('filters', array(
      'status' => array(
        'operator' => '=',
        'value' => 1,
        'group' => '0',
        'exposed' => FALSE,
        'expose' => array(
          'operator' => FALSE,
          'label' => '',
        ),
        'id' => 'status',
        'table' => 'node',
        'field' => 'status',
        'relationship' => 'none',
      ),
    ));
    $handler->override_option('access', array(
      'type' => 'role',
      'role' => drupal_map_assoc($flag->roles),
      'perm' => '',
    ));

    $style_options = array(
      'columns' => array(),
      'default' => module_exists('comment') ? 'last_comment_timestamp' : 'title',
      'info' => array(
        'type' => array(
          'sortable' => TRUE,
        ),
        'title' => array(
          'sortable' => TRUE,
        ),
        'name' => array(
          'sortable' => TRUE,
        ),
      ),
      'override' => FALSE,
      'order' => 'asc',
    );

    if (module_exists('comment')) {
      $style_options['info'] += array(
        'comment_count' => array(
          'sortable' => TRUE,
        ),
        'last_comment_timestamp' => array(
          'sortable' => TRUE,
        ),
      );
    }

    $handler->override_option('title', $flag->global ? $flag->title : t('My !flag-title', array('!flag-title' => $flag->title)));
    $handler->override_option('items_per_page', '25');
    $handler->override_option('use_pager', TRUE);
    $handler->override_option('style_plugin', 'table');
    $handler->override_option('style_options', $style_options);
    $handler = $view->new_display('page', 'Page', 'page_1');
    $handler->override_option('path', 'flags/'. $flag->name);
    $handler->override_option('menu', array(
      'type' => 'normal',
      'title' => $flag->title,
      'weight' => '0',
    ));
    $handler->override_option('tab_options', array(
      'type' => 'none',
      'title' => NULL,
      'weight' => NULL,
    ));

    $views[$view->name] = $view;
  }
  return $views;
}

