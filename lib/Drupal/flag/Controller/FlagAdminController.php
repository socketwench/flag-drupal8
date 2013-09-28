<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 9/27/13
 * Time: 11:02 PM
 */

namespace Drupal\flag\Controller;


class FlagAdminController {

  public function content() {
    $flags = flag_get_flags();
    $default_flags = flag_get_default_flags(TRUE);
    $flag_admin_listing = drupal_get_form('flag_admin_listing', $flags);

    $output = '';

    $output .= drupal_render($flag_admin_listing);
    $output .= theme('flag_admin_listing_disabled', array('flags' => $flags, 'default_flags' => $default_flags));

    if (!module_exists('views')) {
      $output .= '<p>' . t('The <a href="@views-url">Views</a> module is not installed, or not enabled. It is recommended that you install the Views module to be able to easily produce lists of flagged content.', array('@views-url' => url('http://drupal.org/project/views'))) . '</p>';
    }
    else {
      $output .= '<p>';
      $output .= t('Lists of flagged content can be displayed using views. You can configure these in the <a href="@views-url">Views administration section</a>.', array('@views-url' => url('admin/structure/views')));
      if (flag_get_flag('bookmarks')) {
        $output .= ' ' . t('Flag module automatically provides a few <a href="@views-url">default views for the <em>bookmarks</em> flag</a>. You can use these as templates by cloning these views and then customizing as desired.', array('@views-url' => url('admin/structure/views', array('query' => array('tag' => 'flag')))));
      }
      $output .= ' ' . t('The <a href="@flag-handbook-url">Flag module handbook</a> contains extensive <a href="@customize-url">documentation on creating customized views</a> using flags.', array('@flag-handbook-url' => 'http://drupal.org/handbook/modules/flag', '@customize-url' => 'http://drupal.org/node/296954'));
      $output .= '</p>';
    }

    if (!module_exists('flag_actions')) {
      $output .= '<p>' . t('Flagging an item may trigger <em>actions</em>. However, you don\'t have the <em>Flag actions</em> module <a href="@modules-url">enabled</a>, so you won\'t be able to enjoy this feature.', array('@actions-url' => url(FLAG_ADMIN_PATH . '/actions'), '@modules-url' => url('admin/modules'))) . '</p>';
    }
    else {
      $output .= '<p>' . t('Flagging an item may trigger <a href="@actions-url">actions</a>.', array('@actions-url' => url(FLAG_ADMIN_PATH . '/actions'))) . '</p>';
    }

    if (!module_exists('rules')) {
      $output .= '<p>' . t('Flagging an item may trigger <em>rules</em>. However, you don\'t have the <a href="@rules-url">Rules</a> module enabled, so you won\'t be able to enjoy this feature. The Rules module is a more extensive solution than Flag actions.', array('@rules-url' => url('http://drupal.org/node/407070'))) . '</p>';
    }
    else {
      $output .= '<p>' . t('Flagging an item may trigger <a href="@rules-url">rules</a>.', array('@rules-url' => url('admin/config/workflow/rules'))) . '</p>';
    }

    $output .= '<p>' . t('To learn about the various ways to use flags, please check out the <a href="@handbook-url">Flag module handbook</a>.', array('@handbook-url' => 'http://drupal.org/handbook/modules/flag')) . '</p>';

    return array(
      '#type' => 'markup',
      '#markup' => $output,
    );
  }

} 