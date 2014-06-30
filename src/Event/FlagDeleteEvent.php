<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 6/29/14
 * Time: 9:28 PM
 */

namespace Drupal\flag\Event;

use Drupal\flag\FlagInterface;
use Drupal\flag\Event\FlagEventBase;

class FlagDeleteEvent extends FlagEventBase {

  public function __construct(FlagInterface $flag) {
    parent::__construct($flag);
  }
} 