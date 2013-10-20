<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 10/10/13
 * Time: 9:44 PM
 */

namespace Drupal\flag;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

interface FlagInterface extends ConfigEntityInterface {

  // todo: Add getters and setters as necessary.

  public function enable();

  public function disable();

} 