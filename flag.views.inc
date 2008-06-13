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
  $data['flag_content']['flag_ops'] = array(
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
    // Localize the flag title:
    $flag = flag_process_labels($flag, NULL, NULL, array('title'));
    $fid = $flag->fid;
    $view = new stdClass();
    $view->name = 'flags_'. $flag->name;
    $view->access = empty($flag->roles) ? array(DRUPAL_AUTHENTICATED_RID) : $flag->roles;
    $view->description = t('View for flag: !flag-title', array('!flag-title' => $flag->title));
    $view->page = TRUE;
    $view->page_title = t('My !flag-title', array('!flag-title' => $flag->title));
    $view->page_type = 'table';
    $view->url = 'flags/'. $flag->name;
    $view->use_pager = TRUE;
    $view->nodes_per_page = '25';
    $view->menu = TRUE;
    $view->menu_tab = FALSE;
    $view->menu_tab_default = FALSE;
    $view->sort = array(
    );
    $view->argument = array(
    );
    $view->field = array(
      array(
        'tablename' => 'node',
        'field' => 'type',
        'label' => 'Type',
        'sortable' => '1',
      ),
      array(
        'tablename' => 'node',
        'field' => 'title',
        'label' => 'Title',
        'handler' => 'views_handler_field_nodelink_with_flag',
        'sortable' => '1',
      ),
      array(
        'tablename' => 'users',
        'field' => 'name',
        'label' => 'Author',
        'sortable' => '1',
      ),
      array(
        'tablename' => "flag_ops_". $flag->name,
        'field' => 'ops',
        'label' => 'Ops',
      ),
    );
    $view->filter = array(
      array(
        'tablename' => 'node',
        'field' => 'status',
        'operator' => '=',
        'options' => '',
        'value' => '1',
      ),
      array(
        'tablename' => "flag_content_". $flag->name,
        'field' => 'uid',
        'operator' => 'IS NOT',
        'options' => '',
        'value' => '***CURRENT_USER***',
      ),
    );
    $view->requires = array('node', 'users');

    // If comment module exists, append two additional fields to the default
    // view. If comment module is added later, these fields will be added.
    if (module_exists('comment')) {
      $op_field = array_pop($view->field);
      $view->field = array_merge($view->field, array(
        array(
          'tablename' => 'node_comment_statistics',
          'field' => 'comment_count',
          'label' => 'Replies',
          'handler' => 'views_handler_comments_with_new',
          'sortable' => '1',
        ),
        array(
          'tablename' => 'node_comment_statistics',
          'field' => 'last_comment_timestamp',
          'label' => 'Last Post',
          'handler' => 'views_handler_field_since',
          'sortable' => '1',
          'defaultsort' => 'DESC',
        ),
      ));
      array_push($view->field, $op_field);
      $view->requires = array_merge($view->requires, array('node_comment_statistics'));
    }

    $views[$view->name] = $view;
  }
  return $views;
}

