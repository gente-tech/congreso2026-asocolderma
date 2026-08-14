<?php

namespace Drupal\dermau_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Provides a Congreso Benefits Block.
 *
 * @Block(
 *   id = "dermau_congreso_benefits_block",
 *   admin_label = @Translation("Dermau Congreso - Beneficios"),
 * )
 */
class CongresoBenefitsBlock extends BlockBase
{

	/**
	 * {@inheritdoc}
	 */
	public function defaultConfiguration()
	{
		return [
			'title' => '',
			'benefits' => '',
			'image' => [],
			'image_alt' => '',
			'button_label' => '',
			'button_url' => '',
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function blockForm($form, FormStateInterface $form_state)
	{
		$form['title'] = [
			'#type' => 'textfield',
			'#title' => $this->t('Título'),
			'#default_value' => $this->configuration['title'] ?? '',
		];

		$form['benefits'] = [
			'#type' => 'textarea',
			'#title' => $this->t('Beneficios'),
			'#default_value' => $this->configuration['benefits'] ?? '',
			'#description' => $this->t('Escribe un beneficio por línea.'),
		];

		$form['image'] = [
			'#type' => 'managed_file',
			'#title' => $this->t('Imagen'),
			'#upload_location' => 'public://congreso-benefits/',
			'#default_value' => $this->configuration['image'] ?? [],
			'#multiple' => FALSE,
		];

		$form['image_alt'] = [
			'#type' => 'textfield',
			'#title' => $this->t('Texto alternativo de la imagen'),
			'#default_value' => $this->configuration['image_alt'] ?? '',
		];

		$form['button_label'] = [
			'#type' => 'textfield',
			'#title' => $this->t('Texto del botón'),
			'#default_value' => $this->configuration['button_label'] ?? '',
		];

		$form['button_url'] = [
			'#type' => 'textfield',
			'#title' => $this->t('URL del botón'),
			'#default_value' => $this->configuration['button_url'] ?? '',
			'#description' => $this->t('Ejemplo: /inscripciones o https://dominio.com/inscripciones'),
		];

		return $form;
	}

	/**
	 * {@inheritdoc}
	 */
	public function blockSubmit($form, FormStateInterface $form_state)
	{
		$image = $form_state->getValue('image') ?: [];

		if (!empty($image[0])) {
			$file = File::load($image[0]);

			if ($file) {
				$file->setPermanent();
				$file->save();
			}
		}

		$this->configuration['title'] = trim(
			(string) $form_state->getValue('title')
		);

		$this->configuration['benefits'] = trim(
			(string) $form_state->getValue('benefits')
		);

		$this->configuration['image'] = $image;

		$this->configuration['image_alt'] = trim(
			(string) $form_state->getValue('image_alt')
		);

		$this->configuration['button_label'] = trim(
			(string) $form_state->getValue('button_label')
		);

		$this->configuration['button_url'] = trim(
			(string) $form_state->getValue('button_url')
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function build()
	{
		$image_url = '';

		if (!empty($this->configuration['image'][0])) {
			$file = File::load($this->configuration['image'][0]);

			if ($file) {
				$image_url = \Drupal::service('file_url_generator')
					->generateAbsoluteString($file->getFileUri());
			}
		}

		$benefits_text = trim(
			(string) ($this->configuration['benefits'] ?? '')
		);

		$benefits = [];

		if ($benefits_text !== '') {
			$benefits = preg_split('/\R/u', $benefits_text);

			$benefits = array_values(
				array_filter(
					array_map(
						static fn($benefit) => trim((string) $benefit),
						$benefits
					),
					static fn($benefit) => $benefit !== ''
				)
			);
		}

		return [
			'#theme' => 'dermau_congreso_benefits_block',
			'#title' => $this->configuration['title'] ?? '',
			'#benefits' => $benefits,
			'#image' => [
				'url' => $image_url,
				'alt' => $this->configuration['image_alt'] ?? '',
			],
			'#button_label' => $this->configuration['button_label'] ?? '',
			'#button_url' => $this->configuration['button_url'] ?? '',
			'#cache' => [
				'max-age' => 0,
			],
		];
	}
}
