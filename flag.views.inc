<?php
// $Id$

/**
 * @file
 * Provides support for the Views module.
 */

/**
 * Implementation of hook_views_tables
 */
function flag_views_tables() {
  $flags = flag_get_flags();
  foreach ($flags as $flag) {
    // Localize the flag title:
    $flag = _flag_process_labels($flag, NULL, array('title'));
    $table = array(
      'name' => 'flag_content',
      'join' => array(
        'left' => array(
          'table' => 'node',
          'field' => 'nid'
        ),
        'right' => array(
          'field' => 'content_id'
        ),
        'extra' => array(
          'fid' => $flag->fid
        ),
      ),
      'fields' => array(
        'timestamp' => array(
          'name' => t('Flag: @s Flagged Time', array('@s' => $flag->title)),
          'sortable' => TRUE,
          'handler' => views_handler_field_dates(),
          'option' => 'string',
          'help' => t('Display the time the node was flagged by a user.')
                  .' '. t('The option field may be used to specify the custom date format as it\'s required by the date() function or if "as time ago" has been chosen to customize the granularity of the time interval.'),
        ),
      ),
      'filters' => array(
        'uid' => array(
          'name' => t('Flag: @s', array('@s' => $flag->title)),
          // Do not be concerned about reversal here. IS NOT NULL means flagged.
          'operator' => array('IS NOT' => 'Is Flagged By'),
          'list' => array('***CURRENT_USER***' => t('Currently Logged In User'), 'uid' => t('Any User')),
          'list-type' => 'select',
          'handler' => 'flag_filter_handler_uid',
          'help' => t('This will filter a only nodes that have the %s flag. If using <em>Any User</em>, often the <em>Node: Distinct</em> filter will also be desired. The <em>Any User</em> filter is not necessary if combining with other flag sorts or arguments.', array('%s' => $flag->title)),
          'fid' => $flag->fid,
        ),
        'timestamp' => array(
          'name' => t('Flag: @s Flagged Time', array('@s' => $flag->title)),
          'operator' => 'views_handler_operator_gtlt',
          'value' => views_handler_filter_date_value_form(),
          'handler' => 'views_handler_filter_timestamp',
          'option' => 'string',
          'help' => t('This filter allows nodes to be filtered by the time they were flagged by a user.')
            .' '. views_t_strings('filter date'),
        ),
      ),
      'sorts' => array(
        'timestamp' => array(
          'name' => t('Flag: @s Flagged Time', array('@s' => $flag->title)),
          'help' => t('Sort by the time a node was flagged using the %s flag.', array('%s' => $flag->title)),
        ),
      ),
    );
    $tables['flag_content_'. $flag->name] = $table;

    $table = array(
      'name' => 'users',
      'join' => array(
        'left' => array(
          'table' => 'flag_content_'. $flag->name,
          'field' => 'uid',
        ),
        'right' => array(
          'field' => 'uid'
        ),
      ),
      'fields' => array(
        'name' => array(
          'name' => t('Flag: User for @s', array('@s' => $flag->title)),
          'handler' => 'views_handler_field_username',
          'sortable' => TRUE,
          'help' => t('This will display the user who flagged the node.'),
          'uid' => 'uid',
          'addlfields' => array('uid'),
        ),
      ),
      'sorts' => array(
        'name' => array(
          'name' => t('Flag: User for @s', array('@s' => $flag->title)),
          'help' => t('Sort by the the name of the users who flagged the node using the %s flag. This probably makes sense only when the list is filtered for a single node; e.g., on a "Who\'s flagged this" tab.', array('%s' => $flag->title)),
        ),
      ),
    );
    $tables["flag_users_". $flag->name] = $table;

    $table = array(
      'name' => 'flag_types',
      'provider' => 'flag',
      'join' => array(
        'left' => array(
          'table' => 'node',
          'field' => 'type'
        ),
        'right' => array(
          'field' => 'node_type'
        ),
        'extra' => array(
          'fid' => $flag->fid
        ),
      ),
    );
    $tables['flag_types_'. $flag->name] = $table;

    $table = array(
      'name' => 'flag_counts',
      'provider' => 'flag',
      'join' => array(
        'left' => array(
          'table' => 'node',
          'field' => 'nid'
        ),
        'right' => array(
          'field' => 'content_id'
        ),
        'extra' => array(
          'fid' => $flag->fid
        ),
      ),
      'fields' => array(
        'count' => array(
          'name' => t('Flag: Count for @s', array('@s' => $flag->title)),
          'sortable' => true,
          'handler' => array(
            'views_handler_field_int' => t('Display the count as an integer'),
            'flag_field_handler_count' => t('Display the count in a phrase'),
          ),
          'option' => 'string',
          'help' => t('If you choose <em>Display the count in a phrase</em>, use the Option field plus the <code>@count</code> placeholder to craft your phrase. A pipe character may separate the singular form from the plural form. Another pipe may separate a leading zero phrase --which may well be an empty string-- from the aforementioned two. Examples: <code>"@count", "1 vote|@count votes", "needs voting|1 vote|@count votes", "|1 vote|@count votes", "|@count|@count"</code>.'),
        ),
      ),
      'filters' => array(
        'count' => array(
          'name' => t('Flag: Count for @s', array('@s' => $flag->title)),
          'help' => t('This will filter by the number of times that a node has been flagged.'),
          'operator' => 'views_handler_operator_gtlt',
          'option' => 'integer',
        ),
      ),
      'sorts' => array(
        'count' => array(
          'name' => t('Flag: @s Count', array('@s' => $flag->title)),
          'help' => t('Sort by the total flag count for each node.'),
        ),
      ),
    );
    $tables['flag_counts_'. $flag->name] = $table;

    $table = array(
      'name' => 'flag_content',
      'join' => array(
        'left' => array(
          'table' => 'node',
          'field' => 'nid',
        ),
        'right' => array(
          'field' => 'content_id',
        ),
        'extra' => array(
          'fid' => $flag->fid,
          'uid' => $flag->global ? 0 : '***CURRENT_USER***',
          // Note: since this is a LEFT JOIN, we also get records where
          // uid is empty; that is, nodes which haven't been flagged.
          // This is intentional, as we want to display the Ops field
          // for unflagged nodes too.
          // The Ops field handler utilizes uid being empty (NULL) to
          // efficiently figure out whether or not a node is flagged.
        ),
      ),
      'fields' => array(
        'ops' => array(
          'field' => 'uid',
          'name' => t('Flag: Ops for @s', array('@s' => $flag->title)),
          'help' => t('Display an AJAX link to flag, or unflag, the node. Note that the operation is made on behalf of the user who <em>browses the page</em> and not the user whose name happens to appear adjacent to the link.'),
          'handler' => 'flag_field_handler_ops',
          'query_handler' => 'flag_query_handler_ops',
          'fid' => $flag->fid,
        ),
      ),
    );
    $tables["flag_ops_". $flag->name] = $table;
  }

  return $tables;
}

/**
 * Handler that prints the proper operations for a flag.
 */
function flag_field_handler_ops($fieldinfo, $fielddata, $value, $data) {
  $flag = flag_get_flag(NULL, $fieldinfo['fid']);

  $node_type_field = 'flag_types_'. $flag->name .'_node_type';
  if (!$data->$node_type_field) {
    // Flag does not apply to this node.
    return;
  }

  $flags = flag_get_flags(NULL, NULL, $GLOBALS['user']);
  if (!isset($flags[$flag->fid])) {
    // Flag does not apply to this user.
    return;
  }

  // Token replacements.
  $flag = _flag_process_labels($flag, node_load($data->nid), array('flag', 'flag_long'));

  $flag_html = l($flag->flag_short, 'flag/flag/'. $flag->name .'/node/'. $data->nid, array('class' => "flag-flag links_flag_". $flag->name, 'title' => $flag->flag_long), drupal_get_destination());
  $unflag_html = l($flag->unflag_short, 'flag/unflag/'. $flag->name .'/node/'. $data->nid, array('class' => "flag-unflag links_flag_". $flag->name, 'title' => $flag->unflag_long), drupal_get_destination());

  flag_add_js($data->nid, $flag->name, $flag_html, $unflag_html);

  // The value of this field is NULL if there isn't a record for this
  // node in the 'flag_content' table. That is, value is NULL if
  // this node isn't flagged.
  if (!is_null($value)) {
    return $unflag_html;
  }
  else {
    return $flag_html;
  }
}

/**
 * This handler loads the type of the node so the display handler, above,
 * can determine if the flag applies to it.
 */
function flag_query_handler_ops($field, $fieldinfo, &$query) {
  $fid = $fieldinfo['fid'];
  $flag = flag_get_flag(NULL, $fid);

  // Load the type of the node.
  // I could simply do $query->add_field('type', 'node', 'node_type') and get
  // rid of this pseudo table, defined in hook_views_tables(), but I suspect
  // that it will be easier to port to Views 2 if we don't hardcode the 'node'.
  $query->add_field('node_type', 'flag_types_'. $flag->name, 'flag_types_'. $flag->name .'_node_type');
}

/**
 * Handler that lets us filter nodes to include only those by a particular user.
 */
function flag_filter_handler_uid($op, $filter, $filterinfo, &$query) {
  // Load the views flag to check if it's global.
  $flag = flag_get_flag(NULL, $filterinfo['fid']);

  $tn = $query->add_table($filterinfo['table']);
  $tname = $query->get_table_name($filterinfo['table'], $tn);
  $query->add_where("$tname.uid $filter[operator] NULL");
  if ($filter['value'] == '***CURRENT_USER***' && !$flag->global) {
    $query->add_where("$tname.uid = ***CURRENT_USER***");
  }
}

/**
 * Handler that prints the 'count' field in a zero/singular/plural phrase, localizable.
 */
function flag_field_handler_count($fieldinfo, $fielddata, $value, $data) {
  return _flag_format_plural($value, $fielddata['options']);
}

/**
 * Give an argument handler to limit nodes to those flagged by a uid.
 */
function flag_views_arguments() {
  $flags = flag_get_flags();
  foreach ($flags as $flag) {
    // Localize the flag title:
    $flag = _flag_process_labels($flag, NULL, array('title'));
    $argument = array(
      'name' => t("Flags '@s' for UID", array('@s' => $flag->title)),
      'handler' => "flag_arg_uid",
      'help' => t("This argument allows users to filter a view to nodes flagged with the '%s' flag for the specified user.", array("%s" => $flag->title)),
      'table' => "flag_content_". $flag->name,
    );
    $arguments["flag_content_". $flag->name] = $argument;
  }
  return $arguments;
}

function flag_arg_uid($op, &$query, $argtype, $arg = '') {
  switch ($op) {
    case 'summary':
      // We have to do a couple of kind of ugly things here. First,
      // because we're dynamic we dig some table info out of the
      // arguments array. Second, the default behavior on our
      // pseudotables is to lock the table to current user, but
      // we are overriding that.

      $argdata = _views_get_arguments();
      $table = $argdata[$argtype]['table'];

      $table_data = _views_get_tables();
      $joininfo = $table_data[$table]['join'];
      unset($joininfo['extra']);

      $query->add_table($table, true, 1, $joininfo);
      $query->add_table('users', true, 1, array('left' => array('table' => $table, 'field' => 'uid'), 'right' => array('field' => 'uid')));
      $query->add_field('name', 'users');
      $query->add_field('uid', 'users');
      $query->add_where("$table.uid IS NOT NULL");
      $fieldinfo['field'] = "users.name";
      return $fieldinfo;
      break;
    case 'filter':
      $uid = intval($arg);
      $argdata = _views_get_arguments();
      $table = $argdata[$argtype['type']]['table'];

      $table_data = _views_get_tables();
      $joininfo = $table_data[$table]['join'];
      $joininfo['extra']['uid'] = $uid;

      $query->add_table($table, true, 1, $joininfo);
      $query->add_where("$table.uid IS NOT NULL");
      break;
    case 'link':
      $name = ($query->name ? $query->name : variable_get('anonymous', t('Anonymous')));
      return l($name, "$arg/" . intval($query->uid));
    case 'title':
      if (!$query) {
        return variable_get('anonymous', t('Anonymous'));
      }
      $user = db_fetch_object(db_query("SELECT name FROM {users} WHERE uid = '%d'", $query));
      return $user->name;
  }
}

/**
 * Implementation of hook_views_default_views()
 */
function flag_views_default_views() {
  $flags = flag_get_flags();

  foreach ($flags as $flag) {
    // Localize the flag title:
    $flag = _flag_process_labels($flag, NULL, array('title'));
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

