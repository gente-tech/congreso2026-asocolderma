<?php

declare(strict_types=1);

namespace Drupal\dermau_core\Controller;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\file\FileInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\Component\Utility\Html;
use Drupal\Core\Locale\CountryManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Url;

/**
 * Controlador público de patrocinadores.
 */
final class PatrocinadoresController extends ControllerBase
{

  /**
   * Constructor.
   */
  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManagerService,
    private readonly FileUrlGeneratorInterface $fileUrlGeneratorService,
    private readonly LanguageManagerInterface $languageManagerService,
    private readonly RendererInterface $rendererService,
    private readonly CountryManagerInterface $countryManagerService,
    private readonly ConfigFactoryInterface $configFactoryService,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static
  {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('file_url_generator'),
      $container->get('language_manager'),
      $container->get('renderer'),
      $container->get('country_manager'),
      $container->get('config.factory'),
    );
  }

  /**
   * Página principal.
   */
  public function page(): array
  {
    $node_storage = $this->entityTypeManagerService
      ->getStorage('node');

    $node_ids = $node_storage
      ->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'convenio')
      ->condition('status', NodeInterface::PUBLISHED)
      ->condition('field_activo', 1)
      ->sort('field_orden_visualizacon', 'ASC')
      ->sort('title', 'ASC')
      ->execute();

    $nodes = $node_ids
      ? $node_storage->loadMultiple($node_ids)
      : [];

    $patrocinadores = [];
    $cache_tags = ['node_list:convenio'];

    foreach ($nodes as $node) {
      if (!$node instanceof NodeInterface) {
        continue;
      }

      $node = $this->getCurrentTranslation($node);

      $patrocinadores[] = [
        'id' => (int) $node->id(),
        'nombre' => $node->label(),
        'logo' => $this->getImageData($node, 'field_logo'),
      ];

      $cache_tags = Cache::mergeTags(
        $cache_tags,
        $node->getCacheTags(),
      );
    }

    $settings = $this->configFactoryService->get(
      'dermau_core.patrocinadores_settings'
    );

    $eyebrow_text = trim(
      (string) ($settings->get('eyebrow_text') ?? '')
    );

    $page_title = trim(
      (string) ($settings->get('page_title') ?? '')
    );

    $plan_eyebrow_text = trim(
      (string) ($settings->get('plan_eyebrow_text') ?? '')
    );

    $plan_title = trim(
      (string) ($settings->get('plan_title') ?? '')
    );

    $plan_description = trim(
      (string) ($settings->get('plan_description') ?? '')
    );

    $plan_button_label = trim(
      (string) ($settings->get('plan_button_label') ?? '')
    );

    $intro_text = trim(
      (string) ($settings->get('intro_text') ?? '')
    );

    if ($intro_text === '') {
      $intro_text = 'Conoce las organizaciones que apoyan el desarrollo académico, científico y profesional del Congreso.';
    }

    $plano_imagen = $this->getConfiguredFileData(
      $settings,
      'plano_imagen_fid',
      $cache_tags,
    );

    $plano_pdf = $this->getConfiguredFileData(
      $settings,
      'plano_pdf_fid',
      $cache_tags,
    );

    $cache_tags = Cache::mergeTags(
      $cache_tags,
      $settings->getCacheTags(),
    );

    return [
      '#theme' => 'patrocinadores_page',
      '#patrocinadores' => $patrocinadores,
      '#eyebrow_text' => $eyebrow_text,
      '#page_title' => $page_title,
      '#intro_text' => $intro_text,
      '#plan_eyebrow_text' => $plan_eyebrow_text,
      '#plan_title' => $plan_title,
      '#plan_description' => $plan_description,
      '#plan_button_label' => $plan_button_label,
      '#plano_imagen' => $plano_imagen,
      '#plano_pdf' => $plano_pdf,
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

  /**
   * Contenido HTML del popup.
   */
  public function detail(NodeInterface $convenio): Response
  {
    if (
      $convenio->bundle() !== 'convenio'
      || !$convenio->isPublished()
      || !$convenio->access('view')
      || (
        $convenio->hasField('field_activo')
        && !$convenio->get('field_activo')->value
      )
    ) {
      throw new NotFoundHttpException();
    }

    $convenio = $this->getCurrentTranslation($convenio);

    $cache_dependencies = [$convenio];



    $relaciones = $this->getEventosYConferencistas(
      $convenio,
      $cache_dependencies,
    );

    $patrocinador = [
      'id' => (int) $convenio->id(),

      'nombre' => $convenio->label(),
      'logo' => $this->getImageData(
        $convenio,
        'field_logo',
        $cache_dependencies,
      ),
      'descripcion' => $this->getFieldValue(
        $convenio,
        'field_descripcion_corta_convenio',
      ),
      'ano_fundacion' => $this->getFieldValue(
        $convenio,
        'field_ano_de_funcacion',
      ),
      'ciudad' => $this->getFieldValue(
        $convenio,
        'field_ciudad_convenio',
      ),
      'stand' => $this->getFieldValue(
        $convenio,
        'field_numero_stand',
      ),
      'piso' => $this->getFieldValue(
        $convenio,
        'field_piso_stand',
      ),
      'ubicacion' => $this->getImageData(
        $convenio,
        'field_ubicacion_stand',
        $cache_dependencies,
      ),
      'link' => $this->getLinkData($convenio),
      'eventos' => $relaciones['eventos'],
      'eventos_count' => count($relaciones['eventos']),
      'docentes' => $relaciones['docentes'],
      'docentes_count' => count($relaciones['docentes']),
    ];

    $build = [
      '#theme' => 'patrocinador_modal',
      '#patrocinador' => $patrocinador,
    ];

    $html = (string) $this->rendererService->renderRoot($build);

    $response = new CacheableResponse($html);

    foreach ($cache_dependencies as $dependency) {
      if ($dependency instanceof EntityInterface) {
        $response->addCacheableDependency($dependency);
      }
    }

    $response->headers->set(
      'Content-Type',
      'text/html; charset=UTF-8',
    );
    return $response;
  }

  /**
   * Traducción actual.
   */
  private function getCurrentTranslation(
    NodeInterface $node,
  ): NodeInterface {
    $language_id = $this->languageManagerService
      ->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)
      ->getId();

    if ($node->hasTranslation($language_id)) {
      return $node->getTranslation($language_id);
    }

    return $node;
  }

  /**
   * Valor simple.
   */
  private function getFieldValue(
    NodeInterface $node,
    string $field_name,
  ): string {
    if (
      !$node->hasField($field_name)
      || $node->get($field_name)->isEmpty()
    ) {
      return '';
    }

    return trim((string) $node->get($field_name)->value);
  }

  /**
   * Imagen.
   */
  private function getImageData(
    NodeInterface $node,
    string $field_name,
    array &$cache_dependencies = [],
  ): array {
    if (
      !$node->hasField($field_name)
      || $node->get($field_name)->isEmpty()
    ) {
      return [];
    }

    $item = $node->get($field_name)->first();
    $file = $item?->entity;

    if (!$file instanceof FileInterface) {
      return [];
    }

    $cache_dependencies[] = $file;

    $values = $item->getValue();

    return [
      'url' => $this->fileUrlGeneratorService
        ->generateString($file->getFileUri()),
      'alt' => trim((string) ($values['alt'] ?? ''))
        ?: $node->label(),
      'title' => trim((string) ($values['title'] ?? '')),
      'width' => (int) ($values['width'] ?? 0),
      'height' => (int) ($values['height'] ?? 0),
    ];
  }

  /**
   * Link externo.
   */
  private function getLinkData(NodeInterface $node): array
  {
    if (
      !$node->hasField('field_link')
      || $node->get('field_link')->isEmpty()
    ) {
      return [];
    }

    $item = $node->get('field_link')->first();

    try {
      return [
        'url' => $item->getUrl()->toString(),
        'title' => trim((string) $item->title)
          ?: 'Visitar sitio web',
      ];
    } catch (\Throwable) {
      return [];
    }
  }

  /**
   * Programas vinculados.
   */
  private function getProgramas(
    NodeInterface $convenio,
    array &$cache_dependencies,
  ): array {
    if (
      !$convenio->hasField('field_programas_vinculados_conve')
      || $convenio
      ->get('field_programas_vinculados_conve')
      ->isEmpty()
    ) {
      return [];
    }

    $programas = [];

    foreach (
      $convenio
        ->get('field_programas_vinculados_conve')
        ->referencedEntities() as $programa
    ) {
      if (
        !$programa instanceof NodeInterface
        || !$programa->isPublished()
        || !$programa->access('view')
      ) {
        continue;
      }

      $programa = $this->getCurrentTranslation($programa);
      $cache_dependencies[] = $programa;

      $programas[] = [
        'id' => (int) $programa->id(),
        'nombre' => $programa->label(),
        'url' => $programa->toUrl()->toString(),
      ];
    }

    return $programas;
  }

  /**
   * Docentes vinculados.
   */
  private function getDocentes(
    NodeInterface $convenio,
    array &$cache_dependencies,
  ): array {
    if (
      !$convenio->hasField('field_docentes_vinculados')
      || $convenio
      ->get('field_docentes_vinculados')
      ->isEmpty()
    ) {
      return [];
    }

    $docentes = [];

    foreach (
      $convenio
        ->get('field_docentes_vinculados')
        ->referencedEntities() as $docente
    ) {
      if (
        !$docente instanceof NodeInterface
        || !$docente->isPublished()
        || !$docente->access('view')
      ) {
        continue;
      }

      $docente = $this->getCurrentTranslation($docente);
      $cache_dependencies[] = $docente;

      $especialidad = '';

      if (
        $docente->hasField('field_especialidad')
        && !$docente->get('field_especialidad')->isEmpty()
        && $docente->get('field_especialidad')->entity
      ) {
        $especialidad_entity = $docente
          ->get('field_especialidad')
          ->entity;

        $especialidad = $especialidad_entity->label();
        $cache_dependencies[] = $especialidad_entity;
      }

      $perfil = $this->getFieldValue(
        $docente,
        'field_perfil_profesional',
      );

      $docentes[] = [
        'id' => (int) $docente->id(),
        'nombre' => $docente->label(),
        'foto' => $this->getImageData(
          $docente,
          'field_foto_docente',
          $cache_dependencies,
        ),
        'especialidad' => $especialidad,
        'perfil' => $perfil,
        'perfil_resumen' => mb_strimwidth(
          $perfil,
          0,
          220,
          '…',
          'UTF-8',
        ),
        'url' => $docente->toUrl()->toString(),
      ];
    }

    return $docentes;
  }

  /**
   * Obtiene los simposios vinculados al patrocinador.
   */
  private function getSimposios(
    NodeInterface $convenio,
    array &$cache_dependencies,
  ): array {
    if (
      !$convenio->hasField('field_simposios_vinculados')
      || $convenio->get('field_simposios_vinculados')->isEmpty()
    ) {
      return [];
    }

    $salas = [
      'sala_1' => 'Sala 1',
      'sala_2' => 'Sala 2',
      'sala_3' => 'Sala 3',
    ];

    $simposios = [];

    foreach (
      $convenio
        ->get('field_simposios_vinculados')
        ->referencedEntities() as $evento
    ) {
      if (
        !$evento instanceof NodeInterface
        || !$evento->isPublished()
        || !$evento->access('view')
      ) {
        continue;
      }

      $evento = $this->getCurrentTranslation($evento);
      $cache_dependencies[] = $evento;

      $tipo = $this->getReferenceLabel(
        $evento,
        'field_tipo_evento',
        $cache_dependencies,
      );

      $tematica = $this->getReferenceLabel(
        $evento,
        'field_tematica_evento',
        $cache_dependencies,
      );

      $sala_key = $this->getFieldValue(
        $evento,
        'field_sala_evento',
      );

      $fecha_value = $this->getFieldValue(
        $evento,
        'field_dia_evento',
      );

      $hora = $this->getEventTime($evento);

      $simposios[] = [
        'id' => (int) $evento->id(),
        'nombre' => $evento->label(),
        'tipo' => $tipo,
        'tematica' => $tematica,
        'sala' => $salas[$sala_key] ?? $sala_key,
        'sala_class' => str_replace('_', '-', $sala_key),
        'fecha' => $this->formatEventDate($fecha_value),
        'hora' => $hora,
        'url' => $evento->toUrl()->toString(),
      ];
    }

    return $simposios;
  }

  /**
   * Obtiene la etiqueta de una referencia.
   */
  private function getReferenceLabel(
    NodeInterface $node,
    string $field_name,
    array &$cache_dependencies,
  ): string {
    if (
      !$node->hasField($field_name)
      || $node->get($field_name)->isEmpty()
    ) {
      return '';
    }

    $entity = $node->get($field_name)->entity;

    if (!$entity instanceof EntityInterface) {
      return '';
    }

    $cache_dependencies[] = $entity;

    return $entity->label();
  }

  /**
   * Formatea la fecha del evento.
   */
  private function formatEventDate(string $value): string
  {
    if ($value === '') {
      return '';
    }

    try {
      $date = new \DateTimeImmutable(
        $value,
        new \DateTimeZone('UTC'),
      );
    } catch (\Throwable) {
      return '';
    }

    $dias = [
      1 => 'LUN',
      2 => 'MAR',
      3 => 'MIÉ',
      4 => 'JUE',
      5 => 'VIE',
      6 => 'SÁB',
      7 => 'DOM',
    ];

    $meses = [
      1 => 'ENE',
      2 => 'FEB',
      3 => 'MAR',
      4 => 'ABR',
      5 => 'MAY',
      6 => 'JUN',
      7 => 'JUL',
      8 => 'AGO',
      9 => 'SEP',
      10 => 'OCT',
      11 => 'NOV',
      12 => 'DIC',
    ];

    $dia_semana = (int) $date->format('N');
    $mes = (int) $date->format('n');

    return sprintf(
      '%s %d %s',
      $dias[$dia_semana],
      (int) $date->format('j'),
      $meses[$mes],
    );
  }

  /**
   * Formatea la hora del evento.
   */
  private function getEventTime(NodeInterface $evento): string
  {
    if (
      !$evento->hasField('field_hora_evento')
      || $evento->get('field_hora_evento')->isEmpty()
    ) {
      return '';
    }

    $item = $evento
      ->get('field_hora_evento')
      ->first();

    $values = $item?->getValue() ?? [];

    $from = (int) ($values['from'] ?? 0);
    $to = (int) ($values['to'] ?? 0);

    if (!$from || !$to) {
      return '';
    }

    return sprintf(
      '%s - %s',
      gmdate('g:i', $from),
      gmdate('g:i A', $to),
    );
  }

  /**
   * Obtiene eventos y conferencistas desde field_programas_vinculados_conve.
   */
  private function getEventosYConferencistas(
    NodeInterface $convenio,
    array &$cache_dependencies,
  ): array {
    if (
      !$convenio->hasField('field_programas_vinculados_conve')
      || $convenio
      ->get('field_programas_vinculados_conve')
      ->isEmpty()
    ) {
      return [
        'eventos' => [],
        'docentes' => [],
      ];
    }

    $salas = [
      'sala_1' => 'Sala 1',
      'sala_2' => 'Sala 2',
      'sala_3' => 'Sala 3',
    ];

    $eventos = [];
    $docentes = [];

    foreach (
      $convenio
        ->get('field_programas_vinculados_conve')
        ->referencedEntities() as $evento
    ) {
      if (
        !$evento instanceof NodeInterface
        || $evento->bundle() !== 'evento'
        || !$evento->isPublished()
        || !$evento->access('view')
      ) {
        continue;
      }

      $evento = $this->getCurrentTranslation($evento);
      $cache_dependencies[] = $evento;

      $tipo = $this->getReferenceLabel(
        $evento,
        'field_tipo_evento',
        $cache_dependencies,
      );

      $tematica = $this->getReferenceLabel(
        $evento,
        'field_tematica_evento',
        $cache_dependencies,
      );

      $sala_key = $this->getFieldValue(
        $evento,
        'field_sala_evento',
      );

      $fecha_value = $this->getFieldValue(
        $evento,
        'field_dia_evento',
      );

      $eventos[] = [
        'id' => (int) $evento->id(),
        'nombre' => $evento->label(),
        'descripcion' => $this->getFieldValue(
          $evento,
          'field_descripcion_evento',
        ),
        'tipo' => $tipo,
        'tematica' => $tematica,
        'sala' => $salas[$sala_key] ?? $sala_key,
        'sala_class' => Html::getClass($sala_key),
        'fecha' => $this->formatEventDate($fecha_value),
        'hora' => $this->getEventTime($evento),
        'url' => Url::fromRoute(
          'entity.node.canonical',
          ['node' => 98],
          ['fragment' => 'simposio-' . $evento->id()]
        )->toString(),
      ];

      if (
        !$evento->hasField('field_conferencistas_del_evento')
        || $evento
        ->get('field_conferencistas_del_evento')
        ->isEmpty()
      ) {
        continue;
      }

      foreach (
        $evento
          ->get('field_conferencistas_del_evento')
          ->referencedEntities() as $relacion
      ) {
        $cache_dependencies[] = $relacion;

        if (
          !$relacion->hasField('field_conferencista')
          || $relacion->get('field_conferencista')->isEmpty()
        ) {
          continue;
        }

        $docente = $relacion
          ->get('field_conferencista')
          ->entity;

        if (
          !$docente instanceof NodeInterface
          || $docente->bundle() !== 'docente'
          || !$docente->isPublished()
          || !$docente->access('view')
        ) {
          continue;
        }

        $docente = $this->getCurrentTranslation($docente);
        $cache_dependencies[] = $docente;

        $docente_id = (int) $docente->id();

        $pais_value = '';

        if (
          $relacion->hasField('field_pais_conferencista')
          && !$relacion
            ->get('field_pais_conferencista')
            ->isEmpty()
        ) {
          $pais_value = trim(
            (string) $relacion
              ->get('field_pais_conferencista')
              ->value
          );
        }

        $pais = $this->getCountryData($pais_value);

        if (isset($docentes[$docente_id])) {
          if (
            empty($docentes[$docente_id]['pais'])
            && !empty($pais['label'])
          ) {
            $docentes[$docente_id]['pais'] = $pais['label'];
            $docentes[$docente_id]['pais_class'] = $pais['class'];
          }

          continue;
        }

        $especialidad = '';

        if (
          $docente->hasField('field_especialidad')
          && !$docente->get('field_especialidad')->isEmpty()
        ) {
          $especialidad_entity = $docente
            ->get('field_especialidad')
            ->entity;

          if ($especialidad_entity instanceof EntityInterface) {
            $especialidad = $especialidad_entity->label();
            $cache_dependencies[] = $especialidad_entity;
          }
        }

        $perfil = $this->getFieldValue(
          $docente,
          'field_perfil_profesional',
        );

        $docentes[$docente_id] = [
          'id' => $docente_id,
          'nombre' => $docente->label(),
          'foto' => $this->getImageData(
            $docente,
            'field_foto_docente',
            $cache_dependencies,
          ),
          'especialidad' => $especialidad,
          'pais' => $pais['label'],
          'pais_class' => $pais['class'],
          'perfil' => $perfil,
          'perfil_resumen' => mb_strimwidth(
            $perfil,
            0,
            235,
            '…',
            'UTF-8',
          ),
          'url' => $docente->toUrl()->toString(),
        ];
      }
    }

    return [
      'eventos' => $eventos,
      'docentes' => array_values($docentes),
    ];
  }

  /**
   * Obtiene nombre y clase CSS del país.
   */
  private function getCountryData(string $value): array
  {
    $value = trim($value);

    if ($value === '') {
      return [
        'label' => '',
        'class' => '',
      ];
    }

    $countries = $this->countryManagerService->getList();
    $candidate_code = strtoupper($value);

    if (isset($countries[$candidate_code])) {
      return [
        'label' => (string) $countries[$candidate_code],
        'class' => strtolower($candidate_code),
      ];
    }

    foreach ($countries as $code => $country_label) {
      if (
        mb_strtolower((string) $country_label, 'UTF-8')
        === mb_strtolower($value, 'UTF-8')
      ) {
        return [
          'label' => (string) $country_label,
          'class' => strtolower((string) $code),
        ];
      }
    }

    return [
      'label' => $value,
      'class' => Html::getClass($value),
    ];
  }

  /**
   * Obtiene el PDF global del plano.
   */
  private function getPlanoPdfData(
    ImmutableConfig $settings,
    array &$cache_dependencies,
  ): array {
    $fid = (int) (
      $settings->get('plano_pdf_fid') ?? 0
    );

    if ($fid <= 0) {
      return [];
    }

    $file = $this->entityTypeManagerService
      ->getStorage('file')
      ->load($fid);

    if (!$file instanceof FileInterface) {
      return [];
    }

    $cache_dependencies[] = $file;

    return [
      'url' => $this->fileUrlGeneratorService
        ->generateString($file->getFileUri()),
      'filename' => $file->getFilename(),
    ];
  }

  /**
   * Obtiene un archivo configurado globalmente.
   */
  private function getConfiguredFileData(
    ImmutableConfig $settings,
    string $config_key,
    array &$cache_tags,
  ): array {
    $fid = (int) (
      $settings->get($config_key) ?? 0
    );

    if ($fid <= 0) {
      return [];
    }

    $file = $this->entityTypeManagerService
      ->getStorage('file')
      ->load($fid);

    if (!$file instanceof FileInterface) {
      return [];
    }

    $cache_tags = Cache::mergeTags(
      $cache_tags,
      $file->getCacheTags(),
    );

    return [
      'url' => $this->fileUrlGeneratorService
        ->generateString($file->getFileUri()),
      'filename' => $file->getFilename(),
      'mime_type' => $file->getMimeType(),
    ];
  }
}
