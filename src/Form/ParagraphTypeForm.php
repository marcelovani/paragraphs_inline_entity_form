<?php

namespace Drupal\paragraphs_inline_entity_form\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
/**
 * Provides a selector for paragraph type
 *
 * @internal
 */
class ParagraphTypeForm extends FormBase {

  /**
   * The paragraph storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $paragraphsTypeStorage;

  /**
   * The embed buttom storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $embedButtonStorage;

  /**
   * The image style storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $imageStyleStorage;

  /**
   * The bundle info
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $bundleInfo;

  /**
   * Route match
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The embed button id
   *
   * @var
   */
  protected $embedButtonId;

  /**
   * Constructs a new ParagraphLoginForm.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The paragraph type storage.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $bundle_info
   *   The bundle info interface
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EntityTypeBundleInfoInterface $bundle_info, RouteMatchInterface $route_match) {
    $this->paragraphsTypeStorage = $entity_type_manager->getStorage('paragraphs_type');
    $this->embedButtonStorage = $entity_type_manager->getStorage('embed_button');
    $this->imageStyleStorage = $entity_type_manager->getStorage('image_style');
    $this->routeMatch = $route_match;
    $this->bundleInfo = $bundle_info;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('current_route_match'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'paragraph_type_selector';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#prefix'] = '<div id="paragraphs-wysiwyg">';
    $form['#suffix'] = '</div>';
    $form['selected_bundle'] = [
      '#type' => 'hidden',
      '#value' => '',
    ];
    $form['actions'] = [
      '#type' => 'actions',
      'progress_indicator' => 'throbber',
      '#progress_indicator' => 'throbber',
    ];
    $form['#attached']['library'][] = 'paragraphs_inline_entity_form/dialog';

    $embed_button = $this->embedButtonStorage->load($form_state->getBuildInfo()['args'][0]);
    $allowed_bundles = $embed_button->getTypeSetting('bundles');
    $bundles = $this->getAllowedBundles($allowed_bundles);
    $default_icon = drupal_get_path('module', 'paragraphs_inline_entity_form') . '/images/paragraph_thumb.png';
    foreach ($bundles as $bundle => $label) {
      $icon_url = $default_icon;
      if ( $this->paragraphsTypeStorage->load($bundle)->getIconFile()) {
        $style = $this->imageStyleStorage->load('thumbnail');
        $path =  $this->paragraphsTypeStorage->load($bundle)->getIconFile()->getFileUri();
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
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    return parent::submitForm($form, $form_state);
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
  protected function getAllowedBundles($allowed_bundles = NULL) {
    $bundles = $this->bundleInfo->getBundleInfo('paragraph');
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
