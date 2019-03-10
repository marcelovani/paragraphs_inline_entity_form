<?php

namespace Drupal\paragraphs_inline_entity_form\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\geysir\Ajax\GeysirCloseModalDialogCommand;

/**
 * Functionality to edit a paragraph through a modal.
 */
class ParagraphsForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    syslog(5, 'DDD PF form');
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
    syslog(5, 'DDD PF buildForm');
    $form['#prefix'] = '<div id="geysir-modal-form">';
    $form['#suffix'] = '</div>';

    $form['#token'] = FALSE;

    // Define alternative submit callbacks using AJAX by copying the default
    // submit callbacks to the AJAX property.
//    $submit = &$form['actions']['submit'];
//    $submit['#ajax'] = [
//      'callback' => '::ajaxSave',
//      'event' => 'click',
//      'progress' => [
//        'type' => 'throbber',
//        'message' => NULL,
//      ],
//    ];

    $form['actions'] = [];
    $form['actions']['save_modal'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save and Close'),
      '#button_type' => 'primary',
      '#submit' => [],
      '#ajax' => [
        'callback' => '::ajaxSave',
        'event' => 'click',
      ],
    ];

    //syslog(5, 'DDD PF actions ' . var_export($form['actions'],1));
    //syslog(5, 'DDD PF submit ' . var_export($form['#submit'],1));
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function ajaxSave(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    syslog(5, 'DDD PF ajaxSave');
    // When errors occur during form validation, show them to the user.
    if ($form_state->getErrors()) {
      unset($form['#prefix']);
      unset($form['#suffix']);
      $form['status_messages'] = [
        '#type' => 'status_messages',
        '#weight' => -10,
      ];
      $response->addCommand(new HtmlCommand('#geysir-modal-form', $form));
    }
    else {
      // Get all necessary data to be able to correctly update the correct
      // field on the parent node.
      $route_match = $this->getRouteMatch();
      $parent_entity_type = $route_match->getParameter('parent_entity_type');
      $temporary_data = $form_state->getTemporary();
      $parent_entity_revision = isset($temporary_data['parent_entity_revision']) ?
        $temporary_data['parent_entity_revision'] :
        $route_match->getParameter('parent_entity_revision');
      $field_name = $route_match->getParameter('field');
      $field_wrapper_id = $route_match->getParameter('field_wrapper_id');
      $parent_entity_revision = \Drupal::entityManager()
        ->getStorage($parent_entity_type)
        ->loadRevision($parent_entity_revision);

      // Refresh the paragraphs field.
      $response->addCommand(
        new HtmlCommand(
          '[data-geysir-field-paragraph-field-wrapper=' . $field_wrapper_id . ']',
          $parent_entity_revision->get($field_name)->view('default')
        )
      );

      $response->addCommand(new GeysirCloseModalDialogCommand());
    }
    syslog(5, 'DDD PF ajaxSave end');

    return $response;
  }

}
