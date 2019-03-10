<?php

namespace Drupal\paragraphs_inline_entity_form\Form;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\editor\EditorInterface;
use Drupal\embed\EmbedButtonInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Component\Utility\Tags;
use Drupal\Component\Utility\Unicode;

/**
 * Controller that handle the CKEditor embed form for paragraphs.
 */
class ParagraphsController extends ControllerBase {

  /**
   * Presents the embedded paragraphs update form.
   *
   * @param string $uuid
   *   The UUID of Embedded paragraphs we are going to edit via CKE modal form.
   * @return array
   *   A form array as expected by drupal_render().
   */
  public function editForm($uuid) {
    syslog(5, 'DDD PC controller edit');

    if ($entity = $this->entityTypeManager()
        ->getStorage('paragraph')
        ->loadByProperties(['uuid' => $uuid])) {
      $paragraph = current($entity);
      return $this->entityFormBuilder()->getForm($paragraph);

//      $form = $form_builder->getForm($paragraph, 'paragraphs_inline_entity_edit', []);

    }
  }

  /**
   * Returns a page title.
   *
   * @param \Drupal\embed\EmbedButtonInterface|null $embed_button
   *   The embed button.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   Page title.
   */
//  public function getEditTitle(EmbedButtonInterface $embed_button = NULL) {
//    return  $this->t('Edit %title', ['%title' => $embed_button->label()]);
//  }


  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    // We don't need this title on the Modal because we stay on the same page
    // using a Modal, thus we don't loose context.
    unset($form['#title']);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $form['#prefix'] = '<div id="geysir-modal-form">';
    $form['#suffix'] = '</div>';

    // @TODO: fix problem with form is outdated.
    $form['#token'] = FALSE;

    // Define alternative submit callbacks using AJAX by copying the default
    // submit callbacks to the AJAX property.
    $submit = &$form['actions']['submit'];
    $submit['#ajax'] = [
      'callback' => '::ajaxSave',
      'event' => 'click',
      'progress' => [
        'type' => 'throbber',
        'message' => NULL,
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function ajaxSave(array $form, FormStateInterface $form_state) {

  }

}
