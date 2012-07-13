<?php

/**
 * @file
 * Hooks provided by the Flag module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Define one or more flag types.
 *
 * @return
 *  An array whose keys are flag type names and whose values are properties of
 *  the flag type.
 *  Flag type names must match the content type (in the rest of Drupal: entity
 *  type) a flag type works with.
 *  Properties for flag types are as follows:
 *  - 'title': The main label of the flag type.
 *  - 'description': A longer description shown in the UI when creating a new
 *    flag.
 *  - 'handler': The name of the class implementing this flag type.
 */
function hook_flag_definitions() {
  return array(
    'node' => array(
      'title' => t('Nodes'),
      'description' => t("Nodes are a Drupal site's primary content."),
      'handler' => 'flag_node',
    ),
  );
}

/**
 * Alter flag type definitions provided by other modules.
 *
 * @param $definitions
 *  An array of flag definitions returned by hook_flag_definitions().
 */
function hook_flag_definitions_alter(&$definitions) {
  
}

/**
 * TODO
 */
function hook_flag_options_alter() {
  
}

/**
 * Define default flags.
 */
function hook_flag_default_flags() {

}

/**
 * Allow modules to alter a flag when it is initially loaded.
 *
 * @see flag_get_flags().
 */
function hook_flag_alter(&$flag) {

}

/**
 * Act on a flagging.
 *
 * @param $op
 *  The operation being performed: one of 'flag' or 'unflag'.
 * @param $flag
 *  The flag object.
 * @param $content_id
 *  The id of the content (aka entity) the flag is on.
 * @param $account
 *  The user account performing the action.
 * @param $fcid
 *  The id of the flagging in the {flag_content} table.
 */
function hook_flag($op, $flag, $content_id, $account, $fcid) {

}

/**
 * TODO
 */
function hook_flag_access() {

}

/**
 * TODO
 */
function hook_flag_access_multiple() {

}

/**
 *
 */
function hook_flag_link() {

}

/**
 * TODO
 */
function hook_flag_link_types() {

}

/**
 * TODO
 */
function flag_delete () {

}

/**
 * TODO
 */
function hook_flag_reset() {

}

