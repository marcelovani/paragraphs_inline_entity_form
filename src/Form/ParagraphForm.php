<?php

namespace Drupal\paragraphs_inline_entity_form\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Functionality to edit a paragraph.
 */
class ParagraphForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $route_match = $this->getRouteMatch();
    $parent_entity_type = $route_match->getParameter('parent_entity_type');
    $parent_entity_revision = $route_match->getParameter('parent_entity_revision');
    $delta = $route_match->getParameter('delta');

    $parent_entity_revision = $this->entityTypeManager->getStorage($parent_entity_type)->loadRevision($parent_entity_revision);


    $form['#title'] = $this->t('Edit @delta of @field', [
      '@delta' => $delta,
      '%label' => $parent_entity_revision->label(),
    ]);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $route_match = $this->getRouteMatch();
    $parent_entity_type = $route_match->getParameter('parent_entity_type');
    $parent_entity_revision = $route_match->getParameter('parent_entity_revision');

    $this->entity->setNewRevision(TRUE);
    $this->entity->save();

    $parent_entity_revision = $this->entityTypeManager->getStorage($parent_entity_type)->loadRevision($parent_entity_revision);

    $save_status = $parent_entity_revision->save();

    $form_state->setTemporary(['parent_entity_revision' => $parent_entity_revision->getRevisionId()]);

    return $save_status;
  }

}
