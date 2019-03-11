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

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $this->entity->setNewRevision(TRUE);
    $this->entity->save();
    //@todo add command close box
    //@todo add command update wysiwyg widget
  }

}
