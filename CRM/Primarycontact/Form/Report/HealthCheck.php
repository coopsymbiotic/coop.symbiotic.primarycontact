<?php

/**
 * This report helps to identify:
 *
 * - duplicate primary contacts
 * - missing primary contact relationships
 * - inactive primary contact relationships
 * - missing primary contacts
 * - primary contacts without an email address
 * - primary contacts without an email on hold
 * - primary contacts without a CMS account
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
            'default_weight' => '1',
          ],
        ],
        'group_bys' => [
          'id_b' => [
            'title' => ts('Organization ID'),
            'name' => 'id',
            'default' => TRUE,
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
      'civicrm_membership' => [
        'dao' => 'CRM_Member_DAO_Membership',
        'grouping' => 'member-fields',
        'fields' => [
          'membership_type_id' => [
            'title' => ts('Membership Type'),
            'required' => FALSE,
          ],
          'status_id' => [
            'title' => ts('Membership Status'),
            'required' => FALSE,
          ],
        ],
        'filters' => [
/*
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
*/
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
      'civicrm_relationship' => [
        'dao' => 'CRM_Contact_DAO_Relationship',
        'fields' => [
          'id' => [
            'title' => ts('Relationship ID'),
            'no_display' => TRUE,
            'required' => TRUE,
          ],
          'is_active' => [
            'title' => ts('Active Relationships'),
						'default' => TRUE,
          ],
          'is_permission_a_b' => [
            'title' => ts('Can edit Organization'),
						'default' => TRUE,
          ],
          'is_permission_b_a' => [
            'title' => ts('Editable by Organization*'),
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
/*
          'is_active' => [
            'title' => ts('Relationship is Active'),
            'type' => CRM_Utils_Type::T_INT,
            'operatorType' => CRM_Report_Form::OP_SELECT,
            'options' => $booleanOptions,
          ],
*/
				],
        'order_bys' => [
          'is_active' => [
            'title' => ts('Active Relationship'),
            'name' => 'is_active',
            'order' => 'DESC',
            //'default_weight' => '2',
          ],
        ],
        'group_bys' => [
          'id_rel' => [
            'title' => ts('Relationship ID'),
            'name' => 'id',
            'default' => TRUE,
          ],
        ],
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
            //'no_display' => TRUE,
            'default' => FALSE,
          ],
        ],
        'grouping' => 'relation-fields',
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
/*
          'is_deleted' => [
            'title' => ts('Deleted'),
            'name' => 'is_deleted',
          ],
          'is_deceased' => [
            'title' => ts('Deceased'),
            'name' => 'is_deceased',
          ],
*/
        ],
/*
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
*/
        'order_bys' => [
          'sort_name_a' => [
            'title' => ts('Primary Contact'),
            'name' => 'sort_name',
          ],
        ],
        'group_bys' => [
          'id_a' => [
            'title' => ts('Primary Contact ID'),
            'name' => 'id',
            'default' => TRUE,
          ],
        ],
      ],
      'civicrm_email_a' => [
        'dao' => 'CRM_Core_DAO_Email',
        'grouping' => 'contact_a_fields',
        'fields' => [
          'id_a' => [
            'title' => ts('Contact Email ID'),
            'name' => 'id',
            'no_display' => TRUE,
            'required' => TRUE,
          ],
          'email_a' => [
            'title' => ts('Contact Email'),
            'name' => 'email',
						'default' => TRUE,
          ],
          'on_hold_a' => [
            'title' => ts('Email On Hold'),
            'name' => 'on_hold',
            'required' => TRUE,
          ],
        ],
        'filters' => [
          'on_hold' => [
            'title' => ts('Email is on Hold'),
            'type' => CRM_Utils_Type::T_INT,
            'operatorType' => CRM_Report_Form::OP_SELECT,
            'options' => $booleanOptions,
          ],
				],
      ],
      'civicrm_uf_match' => [
        'dao' => 'CRM_Core_DAO_UFMatch',
        'grouping' => 'contact-account',
        'fields' => [
          'uf_id' => [
            'title' => ts('User ID'),
            'required' => TRUE,
          ],
          'uf_name' => [
            'title' => ts('User Name'),
          ],
        ],
        'order_bys' => [
          'id' => [
            'title' => ts('User ID'),
            'name' => 'id',
          ],
        ],
        'group_bys' => [
          'uf_id' => [
            'title' => ts('User ID'),
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


  /**
   * Construct a full SQL query which returns one page worth of results
   *
   * @param int $offset
   * @param int $rowcount
   * @param null $sort
   * @param bool $includeContactIDs
   * @param bool $justIDs
   * @return string, sql
   */
  public function select() {
    $select = [];
    $this->_columnHeaders = [];

    foreach ($this->_columns as $tableName => $table) {
      if (array_key_exists('fields', $table)) {
        foreach ($table['fields'] as $fieldName => $field) {
          if (!empty($field['required']) || !empty($this->_params['fields'][$fieldName])
          ) {
            $this->_columnHeaders["{$tableName}_{$fieldName}"]['type'] = $field['type'];
            $this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = $field['title'];
						switch ($fieldName) {
            case 'sort_name_a':
            case 'email_a':
            case 'uf_name':
							$select[] = "CASE WHEN COUNT(DISTINCT {$field['dbAlias']}) > 1 THEN CONCAT({$field['dbAlias']}, ' (+', COUNT(DISTINCT {$field['dbAlias']}) - 1, ')') ELSE {$field['dbAlias']} END as {$tableName}_{$fieldName}";
              break;

            case 'uf_id':
							$select[] = "REPLACE(GROUP_CONCAT(DISTINCT {$field['dbAlias']}), ',', ' + ') as {$tableName}_{$fieldName}";
              break;

						case 'is_active':
						case 'is_permission_a_b':
						case 'is_permission_b_a':
						case 'on_hold_a':
							$select[] = "COUNT(DISTINCT CASE WHEN {$field['dbAlias']} THEN contact_a_civireport.id END) as {$tableName}_{$fieldName}";
							break;

						case 'relationship_type_id':
							$select[] = "COUNT(DISTINCT {$field['dbAlias']}) as {$tableName}_{$fieldName}_count";
              break;

						default:
							$select[] = "{$field['dbAlias']} as {$tableName}_{$fieldName}";
						}
					}
        }
      }
    }

    $this->_selectClauses = $select;
    $this->_select = "SELECT DISTINCT " . implode(', ', $select) . " ";

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
					ON ( {$this->_aliases['civicrm_membership']}.contact_id = {$this->_aliases['civicrm_contact_b']}.id )";
  }

  public function where() {
    $this->_whereClauses[] = "{$this->_aliases['civicrm_contact_b']}.contact_type = 'Organization'";
    $this->_whereClauses[] = "IFNULL({$this->_aliases['civicrm_contact_b']}.is_deleted,0) = 0";
    $this->_whereClauses[] = "IFNULL({$this->_aliases['civicrm_contact_b']}.is_deceased,0) = 0";
    $this->_whereClauses[] = "IFNULL({$this->_aliases['civicrm_contact_a']}.is_deleted,0) = 0";
    $this->_whereClauses[] = "IFNULL({$this->_aliases['civicrm_contact_a']}.is_deceased,0) = 0";

		$this->_havingClauses[] = '(' . implode(' OR ', [
			"civicrm_relationship_is_active IS NULL",
			"civicrm_relationship_is_active != 1",
			"civicrm_relationship_is_active = 1 AND civicrm_relationship_is_permission_a_b = 0",
			"civicrm_contact_a_contact_id_a IS NULL",
			"civicrm_email_a_id_a IS NULL",
			"civicrm_email_a_on_hold_a",
			"civicrm_uf_match_uf_id IS NULL",
		]) . ')';

    parent::where();
  }

  public function groupBy() {

    $this->_groupBy = "";
    if (is_array($this->_params['group_bys']) && !empty($this->_params['group_bys'])) {
      foreach ($this->_columns as $tableName => $table) {
        if (array_key_exists('group_bys', $table)) {
          foreach ($table['group_bys'] as $fieldName => $field) {
            if (!empty($this->_params['group_bys'][$fieldName])) {
              $this->_groupBy[] = $field['dbAlias'];
            }
          }
        }
      }
      $this->_groupBy = "GROUP BY " . implode(', ', $this->_groupBy) .  " {$this->_rollup} ";
    }
    else {
		  $this->_groupBy = "GROUP BY {$this->_aliases['civicrm_contact_b']}.id, {$this->_aliases['civicrm_contact_a']}.id, {$this->_aliases['civicrm_relationship']}.id";
    }

		return $this->_groupBy;
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

			if (empty($cols['civicrm_contact_a_sort_name_a'])) {
				$cols['civicrm_contact_a_sort_name_a'] = '<span class="error">MISSING</span>';
			}

			$cols['civicrm_contact_b_sort_name_b'] = self::toContactLink('contact', $cols['civicrm_contact_b_sort_name_b'], $cols['civicrm_contact_b_contact_id_b']);
			$cols['civicrm_contact_a_sort_name_a'] = self::toContactLink('relation', $cols['civicrm_contact_a_sort_name_a'], $cols['civicrm_contact_b_contact_id_b'], $cols['civicrm_relationship_id']);

			if (isset($cols['civicrm_membership_membership_type_id'])) {
				$cols['civicrm_membership_membership_type_id'] = CRM_Member_PseudoConstant::membershipType($cols['civicrm_membership_membership_type_id'], FALSE);
			}
			if (isset($cols['civicrm_membership_status_id'])) {
				$cols['civicrm_membership_status_id'] = CRM_Member_PseudoConstant::membershipStatus($cols['civicrm_membership_status_id'], FALSE);
			}
			if (empty($cols['civicrm_email_a_on_hold_a'])) {
				$cols['civicrm_email_a_on_hold_a'] = '';
			}
      else {
        $cols['civicrm_email_a_on_hold_a'] = '<span class="error">' . $cols['civicrm_email_a_on_hold_a'] . '</span>';
      }
			if (empty($cols['civicrm_uf_match_uf_id'])) {
				$cols['civicrm_uf_match_uf_id'] = '<span class="error">MISSING</span>';
			}

      $active = ($cols['civicrm_relationship_is_active'] != 1)
        ? '<span class="error">' . $cols['civicrm_relationship_is_active'] . '</span>'
        : $cols['civicrm_relationship_is_active'];

			$cols['civicrm_relationship_is_active'] = self::toContactLink('relations', $active, $cols['civicrm_contact_b_contact_id_b']);

/*
			if (isset($cols['civicrm_relationship_is_active'])) {
				$cols['civicrm_relationship_is_active'] = self::toYesNo($cols['civicrm_relationship_is_active']);
			}
			if (isset($cols['civicrm_relationship_is_permission_a_b'])) {
				$cols['civicrm_relationship_is_permission_a_b'] = self::toYesNo($cols['civicrm_relationship_is_permission_a_b']);
			}
			if (isset($cols['civicrm_relationship_is_permission_b_a'])) {
				$cols['civicrm_relationship_is_permission_b_a'] = self::toYesNo($cols['civicrm_relationship_is_permission_b_a']);
			}
*/
		}
  }

	static function toContactLink($action='', $label, $cid, $extra=NULL) {
		if (!is_numeric($cid)) {
			return $label;
		}

    $class='action-item crm-hover-button';
		$title='';
		$default='';
		$target='';
		switch ($action) {
			case 'contact':
				$page = 'civicrm/contact/view';
				$args = "cid=$cid&reset=1";
				$class = '';
				$title = E::ts("View Contact");
				$default = $cid;
        $target = '_blank';
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
    $html = '<a' .
      ' href="' . $link . '"' .
      ' class="' . $class . '"' .
      ' title="' . $title . '"' .
      ($target ? ' target="' . $target . '"' : '') .
      '>' . ($label ?: $default) . '</a>';

		return $html;
	}

	static function toYesNo($value) {
		return $value ? E::ts('Yes') : E::ts('No');
	}

}
