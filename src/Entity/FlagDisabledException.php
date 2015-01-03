<?php
/**
 * @file
 */

namespace Drupal\flag\Entity;


use Drupal\flag\FlagInterface;

/**
 * An exception thrown if an operation is being performed on a disabled flag.
 *
 * @package Drupal\flag\Entity
 */
class FlagDisabledException extends \Exception{

  /**
   * Construct the exception object.
   *
   * @param FlagInterface $flag
   *  The flag related to the exception
   * @param \Exception $previous
   *  Optional. A previous exception, if any.
   */
  public function __construct(FlagInterface $flag, \Exception $previous = NULL) {
    $message = 'The operation on flag @flag could not be performed, the flag is disabled.';
    parent::__construct(t($message, array('@flag' => $flag->label())));
  }

}