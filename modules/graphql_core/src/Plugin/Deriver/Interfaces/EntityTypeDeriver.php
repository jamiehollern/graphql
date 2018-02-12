<?php

namespace Drupal\graphql_core\Plugin\Deriver\Interfaces;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\graphql\Utility\StringHelper;
use Drupal\graphql_core\Plugin\Deriver\EntityTypeDeriverBase;

class EntityTypeDeriver extends EntityTypeDeriverBase {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($basePluginDefinition) {
    foreach ($this->entityTypeManager->getDefinitions() as $typeId => $type) {
      if (!($type instanceof ContentEntityTypeInterface)) {
        continue;
      }

      // Only create a base interface for types that support bundles.
      if (!$type->hasKey('bundle')) {
        continue;
      }

      $this->derivatives[$typeId] = [
        'name' => StringHelper::camelCase($typeId),
        'description' => $this->t("The '@type' entity type.", [
          '@type' => $type->getLabel(),
        ]),
        'type' => "entity:$typeId",
        'interfaces' => $this->getInterfaces($type, $basePluginDefinition),
        'entity_type' => $typeId,
      ] + $basePluginDefinition;
    }

    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

}
