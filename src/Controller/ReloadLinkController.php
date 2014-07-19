<?php

/**
 * @file
 * Contains \Drupal\flag\Controller\ReloadLinkController.
 */

namespace Drupal\flag\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\flag\FlagService;

/**
 * Provides a controller to flag and unflag when routed from a normal link.
 *
 * @package Drupal\flag\Controller
 */
class ReloadLinkController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * @var \Drupal\flag\FlagService
   */
  protected $flagService;

  /**
   * Constructor.
   *
   * @param FlagService $flag
   */
  public function __construct(FlagService $flag)
  {
    $this->flagService = $flag;
  }

  /**
   * Create.
   *
   * @param ContainerInterface $container
   * @return static
   */
  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('flag')
    );
  }

  /**
   * Performs a flagging when called via a route.
   *
   * @param $flag_id
   * @param $entity_id
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *
   * @see \Drupal\flag\Plugin\Reload
   */
  public function flag($flag_id, $entity_id) {
    /* @var \Drupal\flag\FlaggingInterface $flagging */
    $flagging = $this->flagService->flag($flag_id, $entity_id);

    // Redirect back to the entity. A passed in destination query parameter
    // will automatically override this.
    $url_info = $flagging->getFlaggable()->urlInfo();
    return $this->redirect($url_info->getRouteName(), $url_info->getRouteParameters());
  }

  /**
   * Performs a flagging when called via a route.
   *
   * @param $flag_id
   * @param $entity_id
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *
   * @see \Drupal\flag\Plugin\Reload
   */
  public function unflag($flag_id, $entity_id) {
    $this->flagService->unflag($flag_id, $entity_id);

    $flag = $this->flagService->getFlagById($flag_id);
    $entity = $this->flagService->getFlaggableById($flag, $entity_id);

    // Redirect back to the entity. A passed in destination query parameter
    // will automatically override this.
    $url_info = $entity->urlInfo();
    return $this->redirect($url_info->getRouteName(), $url_info->getRouteParameters());
  }

}
