<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 2/7/14
 * Time: 7:50 PM
 */

namespace Drupal\flag;

use Drupal\Core\Entity\EntityAccessController;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

class FlaggingAccessController extends EntityAccessController {

  protected function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    if ($account->id() == 1) {
      return TRUE;
    }

    switch ($operation) {
      case 'view':
      case 'flag':
        return user_access('flag ' . $entity->id(), $account);
        break;

      case 'delete':
      case 'unflag':
        return user_access('unflag' . $entity->id(), $account);
        break;
    }

    return parent::checkAccess($entity, $operation, $langcode, $account);
  }

  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    if ($account->id() == 1) {
      return TRUE;
    }

    //@todo Figure out how to handle the NULL $entity_bundle case.
    return user_access('flag ' . $entity_bundle, $account);
  }

  public function getRoles() {
    $roles = array();

    $roles['flag'] = user_roles(FALSE, 'flag ' . $this->entityTypeId);
    $roles['unflag'] = user_roles(FALSE, 'unflag ' . $this->entityTypeId);

    return $roles;
  }

} 