<?php

declare(strict_types=1);

namespace Drupal\dermau_core\Controller;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\file\FileInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controlador de la página pública de patrocinadores.
 */
final class PatrocinadoresController extends ControllerBase {

  /**
   * Constructor.
   */
  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManagerService,
    private readonly FileUrlGeneratorInterface $fileUrlGeneratorService,
    private readonly LanguageManagerInterface $languageManagerService,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('file_url_generator'),
      $container->get('language_manager'),
    );
  }

  /**
   * Renderiza la página de patrocinadores.
   */
  public function page(): array {
    $node_storage = $this->entityTypeManagerService
      ->getStorage('node');

    $query = $node_storage
      ->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'convenio')
      ->condition('status', NodeInterface::PUBLISHED)
      ->condition('field_activo', 1)
      ->sort('field_orden_visualizacon', 'ASC')
      ->sort('title', 'ASC');

    $node_ids = $query->execute();

    $nodes = $node_ids
      ? $node_storage->loadMultiple($node_ids)
      : [];

    $language_id = $this->languageManagerService
      ->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)
      ->getId();

    $patrocinadores = [];
    $cache_tags = ['node_list:convenio'];

    foreach ($nodes as $node) {
      if (
        $node->hasTranslation($language_id)
        && $node->getTranslation($language_id)->isPublished()
      ) {
        $node = $node->getTranslation($language_id);
      }

      $logo_url = '';
      $logo_alt = $node->label();

      if (
        $node->hasField('field_logo')
        && !$node->get('field_logo')->isEmpty()
      ) {
        $logo_item = $node->get('field_logo')->first();
        $logo_file = $logo_item?->entity;
        $logo_values = $logo_item?->getValue() ?? [];

        if ($logo_file instanceof FileInterface) {
          $logo_url = $this->fileUrlGeneratorService
            ->generateString($logo_file->getFileUri());
        }

        $configured_alt = trim(
          (string) ($logo_values['alt'] ?? '')
        );

        if ($configured_alt !== '') {
          $logo_alt = $configured_alt;
        }
      }

      $patrocinadores[] = [
        'id' => (int) $node->id(),
        'nombre' => $node->label(),
        'logo_url' => $logo_url,
        'logo_alt' => $logo_alt,
      ];

      $cache_tags = Cache::mergeTags(
        $cache_tags,
        $node->getCacheTags(),
      );
    }

    return [
      '#theme' => 'patrocinadores_page',
      '#patrocinadores' => $patrocinadores,
      '#attached' => [
        'library' => [
          'dermau_core/patrocinadores',
        ],
      ],
      '#cache' => [
        'tags' => $cache_tags,
        'contexts' => [
          'languages:language_content',
          'user.permissions',
        ],
      ],
    ];
  }

}
