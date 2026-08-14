<?php

namespace Drupal\derma_menu\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * @Block(
 *   id = "derma_menu_block",
 *   admin_label = @Translation("Derma Custom Menu")
 * )
 */
class DermaMenuBlock extends BlockBase implements ContainerFactoryPluginInterface
{

  protected MenuLinkTreeInterface $menuTree;

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    MenuLinkTreeInterface $menu_tree
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->menuTree = $menu_tree;
  }

  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('menu.link_tree')
    );
  }

  public function defaultConfiguration()
  {
    return [
      'campus_button_label' => '',
      'campus_button_url' => '',
    ];
  }

  public function blockForm($form, FormStateInterface $form_state)
  {
    $form['campus_button_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Texto del botón Campus'),
      '#default_value' => $this->configuration['campus_button_label'] ?? '',
    ];

    $form['campus_button_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL del botón Campus'),
      '#default_value' => $this->configuration['campus_button_url'] ?? '',
    ];

    return $form;
  }

  public function blockSubmit($form, FormStateInterface $form_state)
  {
    $this->configuration['campus_button_label'] = trim(
      (string) $form_state->getValue('campus_button_label')
    );

    $this->configuration['campus_button_url'] = trim(
      (string) $form_state->getValue('campus_button_url')
    );
  }

  public function build()
  {

    $menu_name = 'main';

    $parameters = new MenuTreeParameters();
    $parameters->setMaxDepth(2);
    $parameters->onlyEnabledLinks();

    $tree = $this->menuTree->load($menu_name, $parameters);

    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];

    $tree = $this->menuTree->transform($tree, $manipulators);

    return [
      '#theme' => 'derma_menu_block',
      '#items' => $tree,
      '#campus_button_label' => trim(
        (string) ($this->configuration['campus_button_label'] ?? '')
      ),
      '#campus_button_url' => trim(
        (string) ($this->configuration['campus_button_url'] ?? '')
      ),
      '#cache' => [
        'contexts' => ['route'],
        'tags' => ['config:system.menu.' . $menu_name],
      ],
    ];
  }
}
