<?php

namespace Drupal\paragraphs_inline_entity_form\Plugin\EntityBrowser\Display;

use Drupal\entity_browser\Plugin\EntityBrowser\Display\IFrame;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\entity_browser\DisplayRouterInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenDialogCommand;
use Drupal\Component\Utility\Html;
use Drupal\Core\Ajax\HtmlCommand;

/**
 * Presents entity browser in an iFrame.
 *
 * @EntityBrowserDisplay(
 *   id = "paragraph_type",
 *   label = @Translation("Paragraph Type"),
 *   description = @Translation("Displays the paragraph types."),
 *   uses_route = TRUE
 * )
 */
class ParagraphType extends IFrame implements DisplayRouterInterface {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'width' => '100%',
      'height' => '500',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function displayEntityBrowser(array $element, FormStateInterface $form_state, array &$complete_form, array $persistent_data = []) {
    //$entity_browser = parent::displayEntityBrowser($element, $form_state, $complete_form, $persistent_data);
    $width = $this->configuration['width'];
    $height = $this->configuration['height'];
    $ebid = $this->configuration['entity_browser_id'];
    $name = 'entity_browser_iframe_' . $ebid;
    $original_path = $this->currentPath->getPath();
    $uuid = $this->getUuid();
    $data = [
      'query_parameters' => [
        'query' => [
          'uuid' => $this->getUuid(),
          'original_path' => $original_path,
        ],
      ],
    ];
    //$src = Url::fromRoute('entity_browser.' . $ebid, [], $data['query_parameters'])->toString();
    $src = Url::fromRoute('paragraphs_inline_entity_form.modal.select_form')->toString();

    return [
      '#theme_wrappers' => ['container'],
      '#attributes' => [
        'class' => [
          'paragraph-entity-browser-iframe-container',
        ],
      ],
      'iframe' => [
        '#allowed_tags' => ['iframe'],
        '#markup' => "<iframe class=\"entity-browser-handle entity-browser-iframe paragraph-type\"
                          src=\"$src\"
                          width=\"$width\"
                          height=\"$height\"
                          data-uuid=\"$uuid\"
                          data-original-path=\"$original_path\"
                          name=\"$name\"
                          id=\"$name\"></iframe>",
      ],
      '#attached' => [
        'library' => ['entity_browser/iframe'],
        'drupalSettings' => [
          'entity_browser' => [
            $this->getUuid() => [
              'auto_open' => TRUE,
            ],
//            'iframe' => [
//              $this->getUuid() => [
//                'src' => $src,
//                'width' => $width,
//                'height' => $height,
//                'js_callbacks' => $callback_event->getCallbacks(),
//                'entity_browser_id' => $ebid,
//                'auto_open' => TRUE,
//              ],
//            ],
          ],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    unset($form['auto_open']);
    unset($form['link_text']);

    return $form;
  }
}
