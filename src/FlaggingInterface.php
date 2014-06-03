<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 10/20/13
 * Time: 5:57 PM
 */

namespace Drupal\flag;

use Drupal\Core\Entity\ContentEntityInterface;

interface FlaggingInterface extends ContentEntityInterface {

  public function getFlag();

} 