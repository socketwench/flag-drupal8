<?php
/**
 * @file
 * Contains the flagging form.
 */

namespace Drupal\flag\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the flagging form for field entry.
 *
 * @package Drupal\flag\Form
 */
class FlaggingForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->t('Update details');

    $route_info = $this->entity->urlInfo('delete-form');
    $actions['delete'] = [
      '#type' => 'link',
      '#title' => $this->t('Delete Flagging'),
      //'#access' => $this->entity->access('delete'),
      '#attributes' => [
        'class' => ['button', 'button--danger'],
      ],
    ];
    $actions['delete'] += $route_info->toRenderArray();

    $actions['delete']['#route_parameters']['flag_id'] = $this->entity->getFlagId();
    $actions['delete']['#route_parameters']['entity_id'] = $this->entity->getFlaggableId();


    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $entity = parent::submit($form, $form_state);
    // $form_state->setRedirect('content_entity_example.contact_list');

    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $entity->save();
  }
}
