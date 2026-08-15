<?php

namespace Drupal\dermau_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Provides a Dermau Footer Block.
 *
 * @Block(
 *   id = "dermau_footer_block",
 *   admin_label = @Translation("Dermau Footer"),
 * )
 */
class FooterBlock extends BlockBase
{

	/**
	 * {@inheritdoc}
	 */
	public function defaultConfiguration()
	{
		return [
			'logo' => [],
			'logo_alt' => '',
			'footer_text' => '',
			'linkedin_url' => '',
			'instagram_url' => '',
			'youtube_url' => '',
			'facebook_url' => '',
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function blockForm($form, FormStateInterface $form_state)
	{
		$form['logo'] = [
			'#type' => 'managed_file',
			'#title' => $this->t('Logo del footer'),
			'#upload_location' => 'public://footer/',
			'#default_value' => $this->configuration['logo'] ?? [],
			'#multiple' => FALSE,
		];

		$form['logo_alt'] = [
			'#type' => 'textfield',
			'#title' => $this->t('Texto alternativo del logo'),
			'#default_value' => $this->configuration['logo_alt'] ?? '',
		];

		$form['footer_text'] = [
			'#type' => 'textarea',
			'#title' => $this->t('Texto informativo'),
			'#default_value' => $this->configuration['footer_text'] ?? '',
			'#rows' => 8,
			'#description' => $this->t('Los saltos de línea se conservarán en el footer.'),
		];

		$form['linkedin_url'] = [
			'#type' => 'textfield',
			'#title' => $this->t('URL de LinkedIn'),
			'#default_value' => $this->configuration['linkedin_url'] ?? '',
		];

		$form['instagram_url'] = [
			'#type' => 'textfield',
			'#title' => $this->t('URL de Instagram'),
			'#default_value' => $this->configuration['instagram_url'] ?? '',
		];

		$form['youtube_url'] = [
			'#type' => 'textfield',
			'#title' => $this->t('URL de YouTube'),
			'#default_value' => $this->configuration['youtube_url'] ?? '',
		];

		$form['facebook_url'] = [
			'#type' => 'textfield',
			'#title' => $this->t('URL de Facebook'),
			'#default_value' => $this->configuration['facebook_url'] ?? '',
		];

		return $form;
	}

	/**
	 * {@inheritdoc}
	 */
	public function blockSubmit($form, FormStateInterface $form_state)
	{
		$logo = $form_state->getValue('logo');

		if (empty($logo) && !empty($this->configuration['logo'])) {
			$logo = $this->configuration['logo'];
		}

		if (!empty($logo[0])) {
			$file = File::load($logo[0]);

			if ($file) {
				$file->setPermanent();
				$file->save();
			}
		}

		$this->configuration['logo'] = $logo ?: [];

		$this->configuration['logo_alt'] = trim(
			(string) $form_state->getValue('logo_alt')
		);

		$this->configuration['footer_text'] = trim(
			(string) $form_state->getValue('footer_text')
		);

		$this->configuration['linkedin_url'] = trim(
			(string) $form_state->getValue('linkedin_url')
		);

		$this->configuration['instagram_url'] = trim(
			(string) $form_state->getValue('instagram_url')
		);

		$this->configuration['youtube_url'] = trim(
			(string) $form_state->getValue('youtube_url')
		);

		$this->configuration['facebook_url'] = trim(
			(string) $form_state->getValue('facebook_url')
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function build()
	{
		$logo_url = '';

		if (!empty($this->configuration['logo'][0])) {
			$file = File::load($this->configuration['logo'][0]);

			if ($file) {
				$logo_url = \Drupal::service('file_url_generator')
					->generateAbsoluteString($file->getFileUri());
			}
		}

		return [
			'#theme' => 'dermau_footer',
			'#logo' => [
				'url' => $logo_url,
				'alt' => trim(
					(string) ($this->configuration['logo_alt'] ?? '')
				),
			],
			'#footer_text' => trim(
				(string) ($this->configuration['footer_text'] ?? '')
			),
			'#linkedin_url' => trim(
				(string) ($this->configuration['linkedin_url'] ?? '')
			),
			'#instagram_url' => trim(
				(string) ($this->configuration['instagram_url'] ?? '')
			),
			'#youtube_url' => trim(
				(string) ($this->configuration['youtube_url'] ?? '')
			),
			'#facebook_url' => trim(
				(string) ($this->configuration['facebook_url'] ?? '')
			),
		];
	}
}
