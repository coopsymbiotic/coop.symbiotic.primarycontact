<?php
// This file declares a managed database record of type "ReportTemplate".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
return array (
  0 =>
  array (
    'name' => 'CRM_Primarycontact_Form_Report_HealthCheck',
    'entity' => 'ReportTemplate',
    'params' =>
    array (
      'version' => 3,
      'label' => 'Primary Contact HealthCheck',
      'description' => 'Identify missing/invalid primary contacts',
      'class_name' => 'CRM_Primarycontact_Form_Report_HealthCheck',
      'report_url' => 'civicrm/report/primarycontact/healthcheck',
      'component' => 'CiviMember',
    ),
  ),
);
