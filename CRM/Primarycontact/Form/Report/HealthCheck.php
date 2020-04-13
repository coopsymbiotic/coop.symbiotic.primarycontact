<?php

/**
 *
 * TODO : help identify...
 * - duplicate contacts
 * - missing/inactive primary contacts
 * - contacts without [valid] email
 * - contacts without [valid] accounts
 */
use CRM_Primarycontact_ExtensionUtil as E;

class CRM_Primarycontact_Form_Report_HealthCheck extends CRM_Report_Form {

  protected $_relType = NULL;
  protected $_summary = NULL;
  protected $_interval = NULL;
  protected $_charts = [];
  protected $_add2groupSupported = FALSE;

  protected $_customGroupExtends = ['Membership'];
  protected $_customGroupGroupBy = FALSE;
  public $_drilldownReport = ['member/detail' => 'Link to Detail Report'];

  /**
   * This report has not been optimised for group filtering.
   *
   * The functionality for group filtering has been improved but not
   * all reports have been adjusted to take care of it. This report has not
   * and will run an inefficient query until fixed.
   *
   * CRM-19170
   *
   * @var bool
   */
  protected $groupFilterNotOptimised = TRUE;

  /**
   * Class constructor.
   */
  public function __construct() {

		$booleanOptions = [
			'' => ts('- Any -'),
			1 => ts('Yes'),
			0 => ts('No'),
		];

    $this->_columns = [
      'civicrm_contact_b' => [
        'dao' => 'CRM_Contact_DAO_Contact',
        'alias' => 'contact_b',
        'fields' => [
          'contact_id_b' => [
            'name' => 'id',
            'no_display' => TRUE,
            'required' => TRUE,
          ],
          'sort_name_b' => [
            'title' => ts('Organization Name'),
            'name' => 'sort_name',
            'required' => TRUE,
          ],
          'display_name_b' => [
            'title' => ts('Organization Display Name'),
            'name' => 'display_name',
          ],
          'contact_sub_type_b' => [
            'title' => ts('Organization Subtype'),
            'name' => 'contact_sub_type',
          ],
        ],
        'filters' => [
          'sort_name_b' => [
            'title' => ts('Organization Name'),
            'name' => 'sort_name',
            'operator' => 'like',
            'type' => CRM_Report_Form::OP_STRING,
          ],
        ],
        'order_bys' => [
          'sort_name_b' => [
            'title' => ts('Organization'),
            'name' => 'sort_name',
            'default_weight' => '2',
          ],
        ],
        'grouping' => 'contact_b_fields',
      ],
      'civicrm_email_b' => [
        'dao' => 'CRM_Core_DAO_Email',
        'alias' => 'email_b',
        'fields' => [
          'email_b' => [
            'title' => ts('Organization Email'),
            'name' => 'email',
          ],
        ],
        'grouping' => 'contact_b_fields',
      ],
      'civicrm_relationship_type' => [
        'dao' => 'CRM_Contact_DAO_RelationshipType',
        'fields' => [
          //'label_a_b' => [
          //  'title' => ts('Relationship A-B '),
          //  'default' => TRUE,
          //],
          'label_b_a' => [
            'title' => ts('Relationship Type'),
            'default' => TRUE,
          ],
        ],
        'grouping' => 'relation-fields',
      ],
      'civicrm_relationship' => [
        'dao' => 'CRM_Contact_DAO_Relationship',
        'fields' => [
          'id' => [
            'title' => ts('Relationship ID'),
            'no_display' => TRUE,
            'required' => TRUE,
          ],
          'is_active' => [
            'title' => ts('Relationship is Active'),
						'default' => TRUE,
          ],
          'is_permission_a_b' => [
            'title' => ts('Can edit Organization'),
						'default' => TRUE,
          ],
          'is_permission_b_a' => [
            'title' => ts('Editable by Organization'),
						'default' => TRUE,
          ],
          'relationship_id' => [
            'title' => ts('Relationship ID'),
            'name' => 'id',
						'no_display' => TRUE,
          ],
          'description' => [
            'title' => ts('Relationship Description'),
          ],
          'start_date' => [
            'title' => ts('Relationship Start Date'),
          ],
          'end_date' => [
            'title' => ts('Relationship End Date'),
          ],
        ],
        'filters' => [
          'is_active' => [
            'title' => ts('Relationship is Active'),
            'type' => CRM_Utils_Type::T_INT,
            'operatorType' => CRM_Report_Form::OP_SELECT,
            'options' => $booleanOptions,
          ],
				],
			],
      'civicrm_contact_a' => [
        'dao' => 'CRM_Contact_DAO_Contact',
        'alias' => 'contact_a',
        'fields' => [
          'contact_id_a' => [
            'name' => 'id',
            'no_display' => TRUE,
            'required' => TRUE,
          ],
          'sort_name_a' => [
            'title' => ts('Primary Contact Name'),
            'name' => 'display_name',
            'required' => TRUE,
          ],
          'display_name_a' => [
            'title' => ts('Primary Contact Display Name'),
            'name' => 'display_name',
          ],
          'contact_sub_type_a' => [
            'title' => ts('Primary Contact Subtype'),
            'name' => 'contact_sub_type',
          ],
          'contact_sub_type_a' => [
            'title' => ts('Primary Contact Subtype'),
            'name' => 'contact_sub_type',
          ],
          'is_deleted' => [
            'title' => ts('Deleted'),
            'name' => 'is_deleted',
          ],
          'is_deceased' => [
            'title' => ts('Deceased'),
            'name' => 'is_deceased',
          ],
        ],
        'filters' => [
          'is_deleted' => [
            'title' => ts('Primary Contact is Deleted'),
            'type' => CRM_Utils_Type::T_INT,
            'operatorType' => CRM_Report_Form::OP_SELECT,
            'options' => $booleanOptions,
          ],
          'is_deceased' => [
            'title' => ts('Primary Contact is Deceased'),
            'type' => CRM_Utils_Type::T_INT,
            'operatorType' => CRM_Report_Form::OP_SELECT,
            'options' => $booleanOptions,
          ],
				],
        'order_bys' => [
          'sort_name_a' => [
            'title' => ts('Primary Contact'),
            'name' => 'sort_name',
            'default_weight' => '4',
          ],
        ],
      ],
      'civicrm_email_a' => [
        'dao' => 'CRM_Core_DAO_Email',
        'fields' => [
          'email_a' => [
            'title' => ts('Primary Contact Email'),
            'name' => 'email',
						'default' => TRUE,
          ],
        ],
        'order_bys' => [
          'email' => [
            'title' => ts('Primary Email'),
            'name' => 'email',
            //'default_weight' => '4',
          ],
        ],
        'grouping' => 'contact_a_fields',
      ],
      'civicrm_uf_match' => [
        'dao' => 'CRM_Core_DAO_UFMatch',
        'fields' => [
          'uf_id' => [
            'title' => ts('User ID'),
          ],
          'uf_name' => [
            'title' => ts('User Name'),
						'default' => TRUE,
          ],
        ],
        'order_bys' => [
          'id' => [
            'title' => ts('User ID'),
            'name' => 'id',
            //'default_weight' => '4',
          ],
        ],
        'grouping' => 'contact_a_fields',
      ],
      'civicrm_membership' => [
        'dao' => 'CRM_Member_DAO_Membership',
        'grouping' => 'member-fields',
        'fields' => [
          'membership_type_id' => [
            'title' => ts('Membership Type'),
            'required' => FALSE,
          ],
        ],
        'filters' => [
          'membership_join_date' => [
            'title' => ts('Member Since'),
            'type' => CRM_Utils_Type::T_DATE,
            'operatorType' => CRM_Report_Form::OP_DATE,
          ],
          'membership_start_date' => [
            'name' => 'start_date',
            'title' => ts('Membership Start Date'),
            'type' => CRM_Utils_Type::T_DATE,
            'operatorType' => CRM_Report_Form::OP_DATE,
          ],
          'membership_end_date' => [
            'name' => 'end_date',
            'title' => ts('Membership End Date'),
            'type' => CRM_Utils_Type::T_DATE,
            'operatorType' => CRM_Report_Form::OP_DATE,
          ],
          'owner_membership_id' => [
            'title' => ts('Primary Membership'),
            'type' => CRM_Utils_Type::T_INT,
            'operatorType' => CRM_Report_Form::OP_INT,
          ],
          'membership_type_id' => [
            'title' => ts('Membership Type'),
            'type' => CRM_Utils_Type::T_INT,
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => CRM_Member_PseudoConstant::membershipType(),
          ],
          'status_id' => [
            'title' => ts('Membership Status'),
            'type' => CRM_Utils_Type::T_INT,
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => CRM_Member_PseudoConstant::membershipStatus(NULL, NULL, 'label'),
          ],
        ],
      ],
    ];
    $this->_tagFilter = TRUE;

    $this->_groupFilter = TRUE;
    parent::__construct();
  }

  public function preProcess() {

    $this->_relType = \Civi::settings()->get('primarycontact_relationship_type_id');
    $this->assign('reportTitle', E::ts('Primary Contact Health Check'));

    parent::preProcess();
  }


  public function select() {
    $select = [];
    $groupBys = FALSE;
    $this->_columnHeaders = [];

    foreach ($this->_columns as $tableName => $table) {

      if (array_key_exists('fields', $table)) {
        foreach ($table['fields'] as $fieldName => $field) {
          if (!empty($field['required']) || !empty($this->_params['fields'][$fieldName])
          ) {

            if ($fieldName == 'membership_type_id') {
              if (empty($this->_params['group_bys']['membership_type_id']) &&
                !empty($this->_params['group_bys']['join_date'])
              ) {
                $select[] = "GROUP_CONCAT(DISTINCT {$field['dbAlias']}  ORDER BY {$field['dbAlias']} ) as {$tableName}_{$fieldName}";
              }
              else {
                $select[] = "{$field['dbAlias']} as {$tableName}_{$fieldName}";
              }
              $this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = $field['title'];
              $this->_columnHeaders["{$tableName}_{$fieldName}"]['operatorType'] = $field['operatorType'] ?? NULL;
            }
            else {
              $select[] = "{$field['dbAlias']} as {$tableName}_{$fieldName}";
              $this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = $field['title'];
              $this->_columnHeaders["{$tableName}_{$fieldName}"]['operatorType'] = $field['operatorType'] ?? NULL;
            }
          }
        }
      }
    }

    $this->_selectClauses = $select;
    $this->_select = "SELECT " . implode(', ', $select) . " ";

//echo ($this->_select); die();
  }

  public function from() {
    $this->_from = "
				FROM civicrm_contact {$this->_aliases['civicrm_contact_b']}
				LEFT JOIN civicrm_relationship {$this->_aliases['civicrm_relationship']}
					ON ( {$this->_aliases['civicrm_relationship']}.contact_id_b = {$this->_aliases['civicrm_contact_b']}.id
					AND {$this->_aliases['civicrm_relationship']}.relationship_type_id = $this->_relType )
				LEFT JOIN civicrm_email {$this->_aliases['civicrm_email_b']}
					ON ( {$this->_aliases['civicrm_email_b']}.contact_id = {$this->_aliases['civicrm_contact_b']}.id
					AND {$this->_aliases['civicrm_email_b']}.is_primary
					)
				LEFT JOIN civicrm_relationship_type {$this->_aliases['civicrm_relationship_type']}
					ON ( {$this->_aliases['civicrm_relationship_type']}.id = {$this->_aliases['civicrm_relationship']}.relationship_type_id )
				LEFT JOIN civicrm_contact {$this->_aliases['civicrm_contact_a']}
					ON ( {$this->_aliases['civicrm_contact_a']}.id = {$this->_aliases['civicrm_relationship']}.contact_id_a )
				LEFT JOIN civicrm_email {$this->_aliases['civicrm_email_a']}
					ON ( {$this->_aliases['civicrm_email_a']}.contact_id = {$this->_aliases['civicrm_contact_a']}.id
					AND {$this->_aliases['civicrm_email_a']}.is_primary
					)
				LEFT JOIN civicrm_uf_match {$this->_aliases['civicrm_uf_match']}
					ON ( {$this->_aliases['civicrm_uf_match']}.contact_id = {$this->_aliases['civicrm_contact_a']}.id )
		";

		// Filter by membership
    $this->_from .= " LEFT JOIN civicrm_membership {$this->_aliases['civicrm_membership']}
					ON ( {$this->_aliases['civicrm_membership']}.contact_id = {$this->_aliases['civicrm_contact_b']}.id )
				LEFT JOIN civicrm_membership_status
					ON ({$this->_aliases['civicrm_membership']}.status_id = civicrm_membership_status.id  )";
  }

  public function where() {
    $this->_whereClauses[] = "{$this->_aliases['civicrm_contact_b']}.contact_type = 'Organization' AND
                              {$this->_aliases['civicrm_contact_b']}.is_deleted = 0";
    parent::where();
  }

	public function postProcess() {
    parent::postProcess();
  }

  /**
   * Alter display of rows.
   *
   * Iterate through the rows retrieved via SQL and make changes for display purposes,
   * such as rendering contacts as links.
   *
   * @param array $rows
   *   Rows generated by SQL, with an array for each row.
   */
  public function alterDisplay(&$rows) {

		foreach ($rows as &$cols) {
			$cols['civicrm_contact_b_sort_name_b'] = self::toContactLink('relations', $cols['civicrm_contact_b_sort_name_b'], $cols['civicrm_contact_b_contact_id_b']);
			$cols['civicrm_contact_a_sort_name_a'] = self::toContactLink('contact', $cols['civicrm_contact_a_sort_name_a'], $cols['civicrm_contact_a_contact_id_a']);

			if (isset($cols['civicrm_relationship_type_label_b_a'])) {
				$cols['civicrm_relationship_type_label_b_a'] = self::toContactLink('relation', $cols['civicrm_relationship_type_label_b_a'], $cols['civicrm_contact_b_contact_id_b'], $cols['civicrm_relationship_id']);
			}
			if (isset($cols['civicrm_relationship_is_deleted'])) {
				$cols['civicrm_relationship_is_deleted'] = self::toYesNo($cols['civicrm_relationship_is_deleted']);
			}
			if (isset($cols['civicrm_relationship_is_deceased'])) {
				$cols['civicrm_relationship_is_deceased'] = self::toYesNo($cols['civicrm_relationship_is_deceased']);
			}
			if (isset($cols['civicrm_relationship_is_active'])) {
				$cols['civicrm_relationship_is_active'] = self::toYesNo($cols['civicrm_relationship_is_active']);
			}
			if (isset($cols['civicrm_relationship_is_permission_a_b'])) {
				$cols['civicrm_relationship_is_permission_a_b'] = self::toYesNo($cols['civicrm_relationship_is_permission_a_b']);
			}
			if (isset($cols['civicrm_relationship_is_permission_b_a'])) {
				$cols['civicrm_relationship_is_permission_b_a'] = self::toYesNo($cols['civicrm_relationship_is_permission_b_a']);
			}
		}
  }

	static function toContactLink($action='', $label, $cid, $extra=NULL) {
		if (empty($cid)) {
			return $label;
		}

    $class='action-item crm-hover-button';
		$title='';
		$default='';
		switch ($action) {
			case 'contact':
				$page = 'civicrm/contact/view';
				$args = "cid=$cid&reset=1";
				$class = '';
				$default = '#' . $cid;
				break;
			case 'relations':
				$page = 'civicrm/contact/view/rel';
				$args = "cid=$cid&reset=1";
				$title = E::ts("View Relationships");
				break;
			case 'relation':
				$page = 'civicrm/contact/view/rel';
				$args = "action=update&reset=1&cid=$cid&id=$extra&rtype=b_a";
				$title = E::ts("Edit Relationship");
				break;
		}
		$link = CRM_Utils_System::url($page, $args);
		$html = '<a class="' . $class . '" title="' . $title . '" href="' . $link . '">' . ($label ?: $default) . '</a>';

		return $html;
	}

	static function toYesNo($value) {
		return $value ? E::ts('Yes') : E::ts('No');
	}

}
