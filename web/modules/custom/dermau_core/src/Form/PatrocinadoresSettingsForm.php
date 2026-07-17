<?php

declare(strict_types=1);

namespace Drupal\dermau_core\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Configuración de la página de patrocinadores.
 */
final class PatrocinadoresSettingsForm extends ConfigFormBase {

  private const CONFIG_NAME = 'dermau_core.patrocinadores_settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'dermau_core_patrocinadores_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return [
      self::CONFIG_NAME,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(
    array $form,
    FormStateInterface $form_state,
  ): array {
    $config = $this->config(self::CONFIG_NAME);

    $plano_imagen_fid = (int) ($config->get('plano_imagen_fid') ?? 0);
    $plano_pdf_fid = (int) ($config->get('plano_pdf_fid') ?? 0);

    $form['intro_text'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Texto introductorio'),
      '#default_value' => (string) (
        $config->get('intro_text')
        ?? 'Conoce las organizaciones que apoyan el desarrollo académico, científico y profesional del Congreso.'
      ),
      '#rows' => 4,
      '#required' => TRUE,
      '#description' => $this->t('Texto mostrado debajo del título de la página.'),
    ];

    $form['plano_imagen'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Plano general en imagen'),
      '#default_value' => $plano_imagen_fid > 0 ? [$plano_imagen_fid] : [],
      '#upload_location' => 'public://patrocinadores/planos',
      '#upload_validators' => [
        'FileExtension' => [
          'extensions' => 'png jpg jpeg webp',
        ],
      ],
      '#multiple' => FALSE,
      '#description' => $this->t('Imagen mostrada después de la grilla de patrocinadores.'),
    ];

    $form['plano_pdf'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Plano completo en PDF'),
      '#default_value' => $plano_pdf_fid > 0 ? [$plano_pdf_fid] : [],
      '#upload_location' => 'public://patrocinadores/planos',
      '#upload_validators' => [
        'FileExtension' => [
          'extensions' => 'pdf',
        ],
      ],
      '#multiple' => FALSE,
      '#description' => $this->t('Archivo PDF descargable mostrado debajo del plano.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(
    array &$form,
    FormStateInterface $form_state,
  ): void {
    $config = $this->config(self::CONFIG_NAME);

    $previous_image_fid = (int) ($config->get('plano_imagen_fid') ?? 0);
    $previous_pdf_fid = (int) ($config->get('plano_pdf_fid') ?? 0);

    $image_value = array_values(array_filter((array) $form_state->getValue('plano_imagen')));
    $pdf_value = array_values(array_filter((array) $form_state->getValue('plano_pdf')));

    $new_image_fid = isset($image_value[0]) ? (int) $image_value[0] : 0;
    $new_pdf_fid = isset($pdf_value[0]) ? (int) $pdf_value[0] : 0;

    $this->persistManagedFile(
      $previous_image_fid,
      $new_image_fid,
      'patrocinadores_plano_imagen',
    );

    $this->persistManagedFile(
      $previous_pdf_fid,
      $new_pdf_fid,
      'patrocinadores_plano_pdf',
    );

    $config
      ->set(
        'intro_text',
        trim((string) $form_state->getValue('intro_text')),
      )
      ->set('plano_imagen_fid', $new_image_fid)
      ->set('plano_pdf_fid', $new_pdf_fid)
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Registra o elimina el uso del archivo administrado.
   */
  private function persistManagedFile(
    int $previous_fid,
    int $new_fid,
    string $usage_type,
  ): void {
    $file_usage = \Drupal::service('file.usage');

    if ($previous_fid > 0 && $previous_fid !== $new_fid) {
      $previous_file = File::load($previous_fid);

      if ($previous_file !== NULL) {
        $file_usage->delete(
          $previous_file,
          'dermau_core',
          $usage_type,
          1,
        );
      }
    }

    if ($new_fid <= 0 || $new_fid === $previous_fid) {
      return;
    }

    $new_file = File::load($new_fid);

    if ($new_file === NULL) {
      throw new \RuntimeException(sprintf(
        'No fue posible cargar el archivo con FID %d.',
        $new_fid,
      ));
    }

    if ($new_file->isTemporary()) {
      $new_file->setPermanent();
      $new_file->save();
    }

    $file_usage->add(
      $new_file,
      'dermau_core',
      $usage_type,
      1,
    );
  }

}
