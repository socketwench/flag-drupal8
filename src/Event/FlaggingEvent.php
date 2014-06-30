<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 6/29/14
 * Time: 7:56 PM
 */

namespace Drupal\flag\Event;


use Drupal\Core\Entity\EntityInterface;
use Drupal\flag\FlagInterface;

class FlaggingEvent extends FlagEventBase {

  protected $entity;
  protected $action;

  public function __construct(FlagInterface $flag, EntityInterface $entity, $action) {
    parent::__construct($flag);

    $this->entity = $entity;
    $this->action = $action;
  }

} 