<?php

namespace Drupal\paragraphs_inline_entity_form\Plugin\EntityBrowser\Widget;

use Drupal\entity_browser_entity_form\Plugin\EntityBrowser\Widget\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;

/**
 * A wrapper for EntityForm to provide a two step form where on the first step
 * the user can select the Entity type and on the second step, to create content
 *
 * @EntityBrowserWidget(
 *   id = "paragraph_entity_form",
 *   label = @Translation("Paragraph form"),
 *   description = @Translation("Provides entity form widget."),
 *   auto_select = FALSE
 * )
 */
class ParagraphEntityForm extends EntityForm {
  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
        'entity_type' => 'paragraph',
        'submit_text' => $this->t('Save paragraph'),
      ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['bundle'] = [
      '#type' => 'select',
      '#title' => $this->t('Bundle'),
      '#options' => ['Paragraph Selector'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * @todo fix the edit form
   */
  public function getForm(array &$original_form, FormStateInterface $form_state, array $additional_widget_parameters) {
    //@todo move to a class
    /** @var \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store */
    $temp_store = \Drupal::service('tempstore.private');
    $temp_store = $temp_store->get('paragraphs_inline_entity_form');
    $entity_data = $temp_store->get('entity_data');

    syslog(5, 'DDD PE getForm');
    //syslog(5, 'DDD PE data ' . var_export($entity_data, 1));

    if ($entity_uuid = paragraphs_inline_entity_form_embed_get_entity_data_value($entity_data, 'uuid')) {
      $temp_store->delete('entity_data');
      syslog(5, 'DDD PE deleted entity data');

      //@todo display edit form
      syslog(5, 'DDD PE show edit form');

      $entity = NULL;
      //@todo inject
      $entity_type_manager = \Drupal::service('entity_type.manager');
      if ($entity = $entity_type_manager
        ->getStorage('paragraph')
        ->loadByProperties(['uuid' => $entity_uuid])) {
        $paragraph = current($entity);

        $entity_form_builder = \Drupal::service('entity.form_builder');
//        $form = $entity_form_builder->getForm($paragraph);
//return $form;

//        $response = new AjaxResponse();
//        $form_state->setRebuild(TRUE);
//        $rebuild_form = $this->formBuilder->rebuildForm('entity_embed_dialog', $form_state, $form);
//        unset($rebuild_form['#prefix'], $rebuild_form['#suffix']);
//        $response->addCommand(new HtmlCommand('#entity-embed-dialog-form', $rebuild_form));
//        $response->addCommand(new SetDialogTitleCommand('', $rebuild_form['#title']));
//        return $response;

        $response = new AjaxResponse();
        //$form = $entity_form_builder->getForm($paragraph, 'paragraphs_inline_entity_form_edit', []);
        $form = $entity_form_builder->getForm($paragraph);

        //$form_array = \Drupal::formBuilder()->getForm('\Drupal\form_test\Form\FormTestCheckboxTypeJugglingForm', $default_value, $return_value);
        $renderer = \Drupal::service('renderer');

        //$html = $renderer->render($form['selection_display']['selected']['items_' . $last_entity_id . '_' . $selected_entity_keys[$key_index]]);
        $html = $renderer->render($form);

        $response->addCommand(
          new ReplaceCommand('#entity_browser_iframe_paragraph_items', 'aaaaaa')
        );

//print_r($form);
        //kint($form);
        //return $form;
        //$paragraph_title = $this->getParagraphTitle($parent_entity_type, $parent_entity_bundle, $field);
        //$response->addCommand(new GeysirOpenModalDialogCommand($this->t('Edit @paragraph_title', ['@paragraph_title' => $paragraph_title]), render($form)));
//        $response->addCommand(new ReplaceCommand(
//          '#entity_browser_iframe_paragraph_items',
//          $form));
//
//        $response->addCommand(new ReplaceCommand(
//          '#entity_browser_iframe_paragraph_items',
//          $form
//        ));

        return $response;

        //@todo inject
//        $form_form_builder = \Drupal::service('entity.form_builder');
//        $edit_form = $form_form_builder->getForm($paragraph);

        //$paragraph_title = $this->getParagraphTitle($parent_entity_type, $parent_entity_bundle, $field);
        //$response->addCommand(new GeysirOpenModalDialogCommand($this->t('Edit @paragraph_title', ['@paragraph_title' => $paragraph_title]), render($form)));
//        $form_builder = \Drupal::service('form_builder');
//        $form_state->setRebuild(TRUE);
//        $rebuild_form = $form_builder->rebuildForm('entity_embed_dialog', $form_state, $edit_form);
//        unset($rebuild_form['#prefix'], $rebuild_form['#suffix']);
//        $form = $rebuild_form;
//        return $form;

        //$edit_form = $form_builder->getForm($paragraph, 'paragraphs_inline_entity_edit', []);
        //$edit_form = $form_builder->getForm($paragraph);
//kint($edit_form);
//        return $edit_form;
      }
    }

    if (empty($this->configuration['entity_type']) || empty($this->configuration['form_mode'])) {
      return ['#markup' => $this->t('The settings for %label widget are not configured correctly.', ['%label' => $this->label()])];
    }

    // Check if we need to show the content type selector form or the entity create form
    if (!empty($form_state->getUserInput()['selected_bundle'])) {
      $this->configuration['bundle'] = $form_state->getUserInput()['selected_bundle'];
    }

    if ($this->configuration['bundle'] == '0') {
      $form = $this->entitySelectorForm($original_form, $form_state, $additional_widget_parameters);

      return $form;
    }

    if ($form_state->has(['entity_browser', 'widget_context'])) {
      $this->handleWidgetContext($form_state->get([
        'entity_browser',
        'widget_context'
      ]));
    }

    $form = parent::getForm($original_form, $form_state, $additional_widget_parameters);
    syslog(5, 'DDD PE ');

    $form['#submit'] = [
      ['Drupal\inline_entity_form\ElementSubmit', 'trigger']
    ];
    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->configuration['submit_text'],
        '#eb_widget_main_submit' => (empty($form_state->getValues())) ? FALSE : TRUE,
        '#attributes' => ['class' => ['is-entity-browser-submit']],
        '#button_type' => 'primary',
      ],
    ];
    $form['actions']['submit']['#ief_submit_trigger'] = TRUE;
    $form['actions']['submit']['#ief_submit_trigger_all'] = TRUE;
    $form['#attached']['drupalSettings']['entity_browser_widget']['auto_select'] = TRUE;
    $form['inline_entity_form'] = [
      '#type' => 'inline_entity_form',
      '#op' => 'add',
      '#entity_type' => $this->configuration['entity_type'],
      '#bundle' => $this->configuration['bundle'],
      '#form_mode' => $this->configuration['form_mode'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public
  function entitySelectorForm(array &$original_form, FormStateInterface $form_state, array $additional_widget_parameters) {
    syslog(5, 'DDD PE Par selector');

    //@todo move to a class
    /** @var \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store */
    $temp_store = \Drupal::service('tempstore.private');
    $temp_store = $temp_store->get('paragraphs_inline_entity_form');
    $temp_store->delete('entity_data');
    syslog(5, 'DDD PE deleted entity data');

    $path_parts = array_values(array_filter($additional_widget_parameters['path_parts']));

    $form['#prefix'] = '<div id="paragraphs-wysiwyg">';
    $form['#suffix'] = '</div>';
    $form['selected_bundle'] = [
      '#type' => 'hidden',
      '#value' => '',
    ];
    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['#attached']['library'][] = 'paragraphs_inline_entity_form/dialog';

    if ($path_parts[0] == 'entity-embed') {
      $embed_button = $this->entityTypeManager->getStorage('embed_button')
        ->load($path_parts[3]);
    }
    $allowed_bundles = $embed_button->getTypeSetting('bundles');
    $bundles = $this->getAllowedBundles($allowed_bundles);
    $paragraphs_type_storage = $this->entityTypeManager->getStorage('paragraphs_type');
    $default_icon = drupal_get_path('module', 'paragraphs_inline_entity_form') . '/images/paragraph_thumb.png';
    foreach ($bundles as $bundle => $label) {
      $icon_url = $default_icon;
      if ($paragraphs_type_storage->load($bundle)->getIconFile()) {
        $style = $this->entityTypeManager->getStorage('image_style')
          ->load('thumbnail');
        $path = $paragraphs_type_storage->load($bundle)
          ->getIconFile()
          ->getFileUri();
        $icon_url = $style->buildUrl($path);
      }

      $form['items'][$bundle] = [
        '#type' => 'image_button',
        '#prefix' => '<div class="paragraphs-wysiwyg-add-type">',
        '#suffix' => '<span>' . $label . '</span></div>',
        '#src' => $icon_url,
        '#value' => $label,
        '#attributes' => [
          'data-paragraph-bundle' => $bundle,
        ],
      ];
    }

    return $form;
  }

  /**
   * Returns a list of allowed Paragraph bundles to add.
   *
   * @param array $allowed_bundles
   *   An array with Paragraph bundles which are allowed to add.
   *
   * @return array
   *   Array with allowed Paragraph bundles.
   */
  protected
  function getAllowedBundles($allowed_bundles = NULL) {
    $bundles = $this->entityTypeBundleInfo->getBundleInfo('paragraph');
    if (is_array($allowed_bundles) && count($allowed_bundles)) {
      // Preserve order of allowed bundles setting.
      $allowed_bundles_order = array_flip($allowed_bundles);
      // Only keep allowed bundles.
      $bundles = array_intersect_key(
        array_replace($allowed_bundles_order, $bundles),
        $allowed_bundles_order
      );
    }

    // Enrich bundles with their label.
    foreach ($bundles as $bundle => $props) {
      $label = empty($props['label']) ? ucfirst($bundle) : $props['label'];
      $bundles[$bundle] = $label;
    }

    return $bundles;
  }
}
