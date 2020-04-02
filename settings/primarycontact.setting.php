<?php
use CRM_Primarycontact_ExtensionUtil as E;

$settings = [
  'primarycontact_relationship_type_id' => [
    'settings_pages' => ['primarycontact' => ['weight' => 1]],
    'group_name' => 'primarycontact',
    'name' => 'primarycontact_relationship_type_id',
    'title' => E::ts('Relationship Type for Primary contacts'),
    'type' => 'Integer',
    'default' => '0',
    'html_type' => 'entity_reference',
    'entity_reference_options' => ['entity' => 'RelationshipType', 'select' => ['minimumInputLength' => 0]],
    'add' => '1.1',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => '',
    'help_text' => NULL,
  ],
];

return $settings;
