<?php

namespace Drupal\paragraphs_inline_entity_form\Controller;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\geysir\Ajax\GeysirOpenModalDialogCommand;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityFieldManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Ajax\HtmlCommand;

/**
 * Controller for all modal dialogs.
 */
class ParagraphsController extends ControllerBase {

  /**
   * Edit a single paragraph.
   */
  public function edit($uuid) {
    $entity_type_manager = \Drupal::service('entity_type.manager');

    $entity = $entity_type_manager->getStorage('paragraph')
      ->loadByProperties(['uuid' => $uuid]);

    /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
    $paragraph = current($entity);

    $response = new AjaxResponse();
    /** @var \Drupal\Core\Entity\EntityFormBuilder $form_builder */
    $form_builder = \Drupal::service('entity.form_builder');
    $form = $form_builder->getForm($paragraph, 'paragraphs_inline_entity_edit', []);
    //$paragraph_title = $this->getParagraphTitle($parent_entity_type, $parent_entity_bundle, $field);
    //$response->addCommand(new GeysirOpenModalDialogCommand($this->t('Edit @paragraph_title', ['@paragraph_title' => $paragraph_title]), render($form)));

    //$form_builder = \Drupal::service('form_builder');
//    $form_state = new FormStateInterface();
//    $rebuild_form = $form_builder->rebuildForm('entity_embed_dialog', $form_state, $form);
//    unset($rebuild_form['#prefix'], $rebuild_form['#suffix']);

    $response->addCommand(new HtmlCommand('#entity-embed-dialog-form', $form));
    //$response->addCommand(new SetDialogTitleCommand('', 'Edit'));

    //$response = new AjaxResponse();
//    $form = $this->entityFormBuilder()->getForm($paragraph, 'paragraphs_inline_entity_edit', []);
//    $response->addCommand(new GeysirOpenModalDialogCommand($this->t('Edit @paragraph_title', ['@paragraph_title' => $paragraph_title]), render($form)));
    return $response;
  }
}
