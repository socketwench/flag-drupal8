<?php
/**
 * @file
 * Contains Drupal\flag\Controller\ReloadLinkController.
 */

namespace Drupal\flag\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\flag\FlagService;

class ReloadLinkController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * @var Drupal\flag\FlagService
   */
  protected $flagService;

  public function __construct(FlagService $flag)
  {
    $this->flagService = $flag;
  }

  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('flag')
    );
  }

  public function flag(Request $request, $flag_id, $entity_id) {
    $flagging = $this->flagService->flag($flag_id, $entity_id);

    // Get the destination.
    $destination = $request->get('destination', $flagging->getFlaggable()->url());

    //@todo SECURITY HOLE. Please fix!
    return new RedirectResponse($destination);
  }

  public function unflag(Request $request, $flag_id, $entity_id) {
    $this->flagService->unflag($flag_id, $entity_id);

    $flag = $this->flagService->getFlagById($flag_id);
    $entity = $this->flagService->getFlaggableById($flag, $entity_id);

    $destination = $request->get('destination', $entity->url());

    //@todo SECURITY HOLE. Please fix!
    return new RedirectResponse($destination);
  }

} 