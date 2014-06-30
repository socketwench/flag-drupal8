<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 6/29/14
 * Time: 6:09 PM
 */

namespace Drupal\flag\Event;


use Drupal\flag\FlagInterface;
use Symfony\Component\EventDispatcher\Event;

abstract class FlagEventBase extends Event{

  protected $flag;

  public function __construct(FlagInterface $flag) {
    $this->flag = $flag;
  }

} 