<?php

/**
 * @file
 *   Contains the flag_entity class.
 */

/**
 * Base entity flag handler.
 */
class flag_entity extends flag_flag {
  /**
   * Adds additional options that are common for all entity types.
   */
  function options() {
    $options = parent::options();
    $options += array(
      // Output the flag in the entity links.
      // @see hook_entity_view().
      'show_on_entity' => TRUE,
      // Add a checkbox for the flag in the entity form.
      // @see hook_field_attach_form().
      'show_on_form' => FALSE,
      'access_author' => '',
    );
    return $options;
  }

  /**
   * Options form extras for the generic entity flag.
   */
  function options_form(&$form) {
    $bundles = array();
    $entity_info = entity_get_info($this->entity_type);
    foreach ($entity_info['bundles'] as $bundle_key => $bundle) {
      $bundles[$bundle_key] = check_plain($bundle['label']);
    }
    $form['access']['types'] = array(
      '#type' => 'checkboxes',
      '#title' => t('Bundles'),
      '#options' => $bundles,
      '#description' => t('Check any bundle that this flag may be used on. You must check at least one bundle.'),
      '#default_value' => $this->types,
      '#required' => TRUE,
    );
    // Handlers may want to unset this option if they provide their own more
    // specific ways to show links.
    $form['display']['show_on_entity'] = array(
      '#type' => 'checkbox',
      '#title' => t('Display link on entity'),
      '#default_value' => isset($this->show_on_entity) ? $this->show_on_entity : TRUE,
      '#access' => empty($this->locked['show_on_entity']),
    );
    $form['display']['show_on_form'] = array(
      '#type' => 'checkbox',
      '#title' => t('Display checkbox on entity edit form'),
      '#default_value' => $this->show_on_form,
      '#access' => empty($this->locked['show_on_form']),
    );
  }

  /**
   * Loads the entity object.
   */
  function _load_content($entity_id) {
    if (is_numeric($entity_id)) {
      $entity = entity_load($this->entity_type, array($entity_id));
      return reset($entity);
    }
    return NULL;
  }

  /**
   * Checks whether the flag applies for the current entity bundle.
   */
  function applies_to_content_object($entity) {
    $entity_info = entity_get_info($this->entity_type);
    if (empty($entity_info['entity keys']['bundle']) || in_array($entity->{$entity_info['entity keys']['bundle']}, $this->types)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Returns the entity id, if it already exists.
   */
  function get_content_id($entity) {
    $entity_info = entity_get_info($this->entity_type);
    if ($entity && isset($entity->{$entity_info['entity keys']['id']})) {
      return $entity->{$entity_info['entity keys']['id']};
    }
  }

  /**
   * Returns TRUE if the link should be displayed.
   */
  function uses_hook_link($teaser) {
    if ($this->show_on_entity) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Returns token types for the current entity type.
   */
  function get_labels_token_types() {
    // The token type name might be different to the entity type name. If so,
    // an own flag entity handler can be used for overriding this.
    return array_merge(array($this->entity_type), parent::get_labels_token_types());
  }

  /**
   * Replaces tokens.
   */
  function replace_tokens($label, $contexts, $options, $entity_id) {
    if ($entity_id && ($entity = $this->fetch_content($entity_id))) {
      $contexts[$this->entity_type] = $entity;
    }
    return parent::replace_tokens($label, $contexts, $options, $entity_id);
  }

  /**
   * Returns a 'flag action' object.
   */
  function get_flag_action($entity_id) {
    $flag_action = parent::get_flag_action($entity_id);
    $entity = $this->fetch_content($entity_id);
    $flag_action->content_title = entity_label($this->entity_type, $entity);
    $flag_action->content_url = _flag_url($this->entity_type . '/' . $this->get_content_id($entity));
    return $flag_action;
  }

  /**
   * Returns objects the action may possible need.
   */
  function get_relevant_action_objects($entity_id) {
    return array(
      $this->entity_type => $this->fetch_content($entity_id),
    );
  }

  /**
   * Returns information for the Views integration.
   */
  function get_views_info() {
    $entity_info = entity_get_info($this->entity_type);
    return array(
      'views table' => $entity_info['base table'],
      'join field' => $entity_info['entity keys']['id'],
      'title field' => isset($entity_info['entity keys']['label']) ? $entity_info['entity keys']['label'] : '',
      'title' => t('@entity_label flag', array('@entity_label' => $entity_info['label'])),
      'help' => t('Limit results to only those entity flagged by a certain flag; Or display information about the flag set on a entity.'),
      'counter title' => t('@entity_label flag counter', array('@entity_label' => $entity_info['label'])),
      'counter help' => t('Include this to gain access to the flag counter field.'),
    );
  }
}
