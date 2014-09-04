<?php
/**
 * @file
 * Contains the FlagListController class.
 */

namespace Drupal\flag\Controller;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\flag\FlagInterface;

/**
 * Provides a entity list page for Flags.
 *
 * @package Drupal\flag\Controller
 */
class FlagListController extends ConfigEntityListBuilder {

  /**
   * Overrides Drupal\Core\Entity\EntityListController::buildHeader().
   */
  public function buildHeader() {
    $header['label'] = t('Flag');
    $header['roles'] = t('Roles');
    $header['is_global'] = t('Global?');

    return $header + parent::buildHeader();
  }

  /**
   * Creates a displayable string of roles that may use the flag.
   *
   * @param FlagInterface $flag
   *   The flag entity.
   *
   * @return string
   *   An HTML sting of roles.
   */
  protected function getFlagRoles(FlagInterface $flag) {
    $out = '';
    $all_roles = [];

    foreach ($flag->getPermissions() as $perm => $pinfo) {
      $roles = user_roles(FALSE, $perm);

      foreach ($roles as $rid => $role) {
        $all_roles[$rid] = $role->label();
      }
    }

    $out = implode(', ', $all_roles);

    if (empty($out)) {
      return '<em>None</em>';
    }

    return rtrim($out, ', ');
  }

  /**
   * Overrides Drupal\Core\Entity\EntityListController::buildRow().
   */
  public function buildRow(EntityInterface $entity) {

    $row['label'] = $this->getLabel($entity);

    $row['roles'] = $this->getFlagRoles($entity);

    $row['is_global'] = $entity->isGlobal() ? t('Yes') : t('No');

    return $row + parent::buildRow($entity);
  }

  /**
   * Overrides Drupal\Core\Entity\EntityListController::render().
   *
   * We override the render() method to add helpful text below the entity list.
   */
  public function render() {
    $build['table'] = parent::render();

    $output = "";

    // @todo Move this too hook_help()?
    if (!\Drupal::moduleHandler()->moduleExists('views')) {
      $output .= '<p>' . t('The <a href="@views-url">Views</a> module is not installed, or not enabled. It is recommended that you install the Views module to be able to easily produce lists of flagged content.', ['@views-url' => url('http://drupal.org/project/views')]) . '</p>';
    }
    else {
      $output .= '<p>';
      $output .= t('Lists of flagged content can be displayed using views. You can configure these in the <a href="@views-url">Views administration section</a>.', ['@views-url' => url('admin/structure/views')]);
      if (\Drupal::service('flag')->getFlagById('bookmarks')) {
        $output .= ' ' . t('Flag module automatically provides a few <a href="@views-url">default views for the <em>bookmarks</em> flag</a>. You can use these as templates by cloning these views and then customizing as desired.', ['@views-url' => url('admin/structure/views', ['query' => ['tag' => 'flag']])]);
      }
      $output .= ' ' . t('The <a href="@flag-handbook-url">Flag module handbook</a> contains extensive <a href="@customize-url">documentation on creating customized views</a> using flags.', ['@flag-handbook-url' => 'http://drupal.org/handbook/modules/flag', '@customize-url' => 'http://drupal.org/node/296954']);
      $output .= '</p>';
    }

    if (!\Drupal::moduleHandler()->moduleExists('flag_actions')) {
      $output .= '<p>' . t('Flagging an item may trigger <em>actions</em>. However, you don\'t have the <em>Flag actions</em> module <a href="@modules-url">enabled</a>, so you won\'t be able to enjoy this feature.', ['@actions-url' => url(FLAG_ADMIN_PATH . '/actions'), '@modules-url' => url('admin/modules')]) . '</p>';
    }
    else {
      $output .= '<p>' . t('Flagging an item may trigger <a href="@actions-url">actions</a>.', ['@actions-url' => url(FLAG_ADMIN_PATH . '/actions')]) . '</p>';
    }

    if (!\Drupal::moduleHandler()->moduleExists('rules')) {
      $output .= '<p>' . t('Flagging an item may trigger <em>rules</em>. However, you don\'t have the <a href="@rules-url">Rules</a> module enabled, so you won\'t be able to enjoy this feature. The Rules module is a more extensive solution than Flag actions.', ['@rules-url' => url('http://drupal.org/node/407070')]) . '</p>';
    }
    else {
      $output .= '<p>' . t('Flagging an item may trigger <a href="@rules-url">rules</a>.', ['@rules-url' => url('admin/config/workflow/rules')]) . '</p>';
    }

    $output .= '<p>' . t('To learn about the various ways to use flags, please check out the <a href="@handbook-url">Flag module handbook</a>.', ['@handbook-url' => 'http://drupal.org/handbook/modules/flag']) . '</p>';

    $build['markup'] = [
      '#type' => 'markup',
      '#markup' => $output,
    ];

    return $build;
  }

}
