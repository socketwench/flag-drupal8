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
 */
class ReloadLinkController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The flag service.
   *
   * @var \Drupal\flag\FlagService
   */
  protected $flagService;

  /**
   * Constructor.
   *
   * @param FlagService $flag
   *   The flag service.
   */
  public function __construct(FlagService $flag) {
    $this->flagService = $flag;
  }

  /**
   * Create.
   *
   * @param ContainerInterface $container
   *   The container object.
   *
   * @return ReloadLinkController
   *   The reload link controller.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('flag')
    );
  }

  /**
   * Performs a flagging when called via a route.
   *
   * @param int $flag_id
   *   The flag ID.
   * @param int $entity_id
   *   The flaggable ID.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   The response object.
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
   * @param int $flag_id
   *   The flag ID.
   * @param int $entity_id
   *   The flagging ID.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   The response object.
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
