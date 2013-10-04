<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 9/30/13
 * Time: 8:58 PM
 */
namespace Drupal\flag\Export;
/**
 * Flag update class for API 1 flags -> API 2.
 *
 * The class name after the prefix is immaterial, though we follow the Drupal
 * system update convention whereby the number here is what we update to.
 */
class FlagUpdate_2 {

  /**
   * The API version this class updates a flag from.
   *
   * @todo: Change this to a class constant when we drop support for PHP 5.2.
   */
  public $old_api_version = 1;

  /**
   * The API version this class updates a flag to.
   */
  public $new_api_version = 2;

  /**
   * The update function for the flag.
   */
  static function update(&$flag) {
    if (isset($flag->roles) && !isset($flag->roles['flag'])) {
      $flag->roles = array(
        'flag' => $flag->roles,
        'unflag' => $flag->roles,
      );
    }
  }
}