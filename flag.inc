<?php

/**
 * Returns a flag definition.
 */
function flag_fetch_definition($entity_type = NULL) {
  $cache = &drupal_static(__FUNCTION__);
  if (!isset($definitions)) {
    if ($cache = cache_get('flag_type_info')) {
      $definitions = $cache->data;
    }
    else {
      $definitions = module_invoke_all('flag_type_info');
      drupal_alter('flag_type_info', $definitions);
      if (!isset($definitions['node'])) {
        // We want our API to be available in hook_install, but our module is not
        // enabled by then, so let's load our implementation directly:
        $definitions += flag_flag_type_info();
      }

      cache_set('flag_type_info', $definitions);
    }
  }

  if (isset($entity_type)) {
    if (isset($definitions[$entity_type])) {
      return $definitions[$entity_type];
    }
  }
  else {
    return $definitions;
  }
}

/**
 * Returns all flag types defined on the system.
 */
function flag_get_types() {
  $types = &drupal_static(__FUNCTION__);
  if (!isset($types)) {
    $types = array_keys(flag_fetch_definition());
  }
  return $types;
}

/**
 * Instantiates a new flag handler.
 *
 * A flag handler is more commonly know as "a flag". A factory method usually
 * populates this empty flag with settings loaded from the database.
 *
 * @param $entity_type
 *  The entity type to create a flag handler for. This may be FALSE if the
 *  entity type property could not be found in the flag configuration data.
 *
 * @return
 *  A flag handler object. This may be the special class flag_broken is there is
 *  a problem with the flag.
 */
function flag_create_handler($entity_type) {
  $definition = flag_fetch_definition($entity_type);
  if (isset($definition) && class_exists($definition['handler'])) {
    $handler = new $definition['handler'];
  }
  else {
    $handler = new flag_broken;
  }
  $handler->entity_type = $entity_type;
  $handler->construct();
  return $handler;
}

/**
 * A shortcut function to output the link URL.
 */
function _flag_url($path, $fragment = NULL, $absolute = TRUE) {
  return url($path, array('fragment' => $fragment, 'absolute' => $absolute));
}
