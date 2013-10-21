<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 10/20/13
 * Time: 5:57 PM
 */

namespace Drupal\flag;


interface FlaggingInterface extends ContentEntityInterface {

  public function getFlag();

} 