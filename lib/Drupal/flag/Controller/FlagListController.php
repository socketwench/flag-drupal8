<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 10/12/13
 * Time: 9:23 PM
 */

namespace Drupal\flag\Controller;

use Drupal\Core\Config\Entity\ConfigEntityListController;
use Drupal\Core\Entity\EntityInterface;

class FlagListController extends ConfigEntityListController {

  /**
   * Overrides Drupal\Core\Entity\EntityListController::buildHeader().
   */
  public function buildHeader() {
    $header['id'] = t('ID');
    $header['label'] = t('Label');

    return $header + parent::buildHeader();
  }

  /**
   * Overrides Drupal\Core\Entity\EntityListController::buildRow().
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $this->getLabel($entity);

    return $row + parent::buildRow($entity);
  }

} 