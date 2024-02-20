<?php

namespace Drupal\cff\Plugin\Field\FieldFormatter;

use Cocur\Slugify\SlugifyInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'slugify_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "slugify_formatter",
 *   label = @Translation("Slugify formatter"),
 *   field_types = {
 *     "string",
 *     "string_long",
 *     "text",
 *   },
 * )
 */
class SlugifyFormatter extends FormatterBase {

  /**
   * The Slugify service.
   *
   * @var \Cocur\Slugify\SlugifyInterface
   */
  protected $slugify;

  /**
   * Constructs a new SlugifyFormatter object.
   *
   * @param string $plugin_id
   *   The plugin_id for the field formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field definition.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Cocur\Slugify\SlugifyInterface $slugify
   *   The Slugify service.
   */
  public function __construct($plugin_id, $plugin_definition, $field_definition, array $settings, $label, $view_mode, array $third_party_settings, SlugifyInterface $slugify) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->slugify = $slugify;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('cff.slugify')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'separator' => '-',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $element['separator'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Separator'),
      '#default_value' => $this->getSetting('separator'),
      '#maxlength' => 1,
      '#size' => 1,
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    return [$this->t('Separator: @separator', ['@separator' => $this->getSetting('separator')])];
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $text = $item->value;
      $separator = $this->getSetting('separator');

      // Replace spaces with the chosen separator.
      $slug = $this->slugify->slugify($text, $separator);

      $elements[$delta] = [
        '#markup' => $slug,
      ];
    }

    return $elements;
  }

}
