<?php
/**
 * @file
 * Contains \Drupal\flag\Plugin\Flag\UserFlagType.
 */

namespace Drupal\flag\Plugin\Flag;

use Drupal\flag\FlagTypeBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class UserFlagType
 * @package Drupal\flag\Plugin\Flag
 *
 * @FlagType(
 *   id = "flagtype_user",
 *   title = @Translation("User"),
 *   entity_type = "user",
 *   provider = "user"
 * )
 */
class UserFlagType extends FlagTypeBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $options = parent::defaultConfiguration();
    $options += array(
      'show_on_profile' => TRUE,
      'access_uid' => '',
    );
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    /* Options form extras for user flags */

    $form['access']['types'] = array(
      // A user flag doesn't support node types.
      // TODO: Maybe support roles instead of node types.
      '#type' => 'value',
      '#value' => array(0 => 0),
    );
    $form['access']['access_uid'] = array(
      '#type' => 'checkbox',
      '#title' => t('Users may flag themselves'),
      '#description' => t('Disabling this option may be useful when setting up a "friend" flag, when a user flagging themself does not make sense.'),
      '#default_value' => $this->configuration['access_uid'] ? 0 : 1,
    );
    $form['display']['show_on_profile'] = array(
      '#type' => 'checkbox',
      '#title' => t('Display link on user profile page'),
      '#description' => t('Show the link formatted as a user profile element.'),
      '#default_value' => $this->configuration['show_on_profile'],
      // Put this above 'show on entity'.
      '#weight' => -1,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $this->configuration['access_uid'] = $form_state['values']['access']['access_uid'];
    $this->configuration['show_on_profile'] = $form_state['values']['display']['show_on_profile'];
  }

  /**
   * {@inheritdoc}
   */
  public function type_access_multiple($entity_ids, $account) {
    $access = array();

    // Exclude anonymous.
    if (array_key_exists(0, $entity_ids)) {
      $access[0] = FALSE;
    }

    // Prevent users from flagging themselves.
    if ($this->access_uid == 'others' && array_key_exists($account->uid, $entity_ids)) {
      $access[$account->uid] = FALSE;
    }

    return $access;
  }

  public function getAccessUidSetting() {
    return $this->configuration['access_uid'];
  }

  public function showOnProfile() {
    return $this->configuration['show_on_profile'];
  }
}
