# coop.symbiotic.primarycontact

![Screenshot](/images/screenshot.png)

(*FIXME: In one or two paragraphs, describe what the extension does and why one would download it. *)

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

* PHP v5.4+
* CiviCRM (*FIXME: Version number*)

## Installation (Web UI)

This extension has not yet been published for installation via the web UI.

## Installation (CLI, Zip)

Sysadmins and developers may download the `.zip` file for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
cd <extension-dir>
cv dl coop.symbiotic.primarycontact@https://github.com/FIXME/coop.symbiotic.primarycontact/archive/master.zip
```

## Installation (CLI, Git)

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://github.com/FIXME/coop.symbiotic.primarycontact.git
cv en primarycontact
```

## Usage

* create a new relationship type that represents the Primary contact relationship. 
* get the id of this relationship type and init the configuration using the api :

```php
$result = civicrm_api3('Setting', 'create', array(
  'sequential' => 1,
  'primarycontact_relationship_type_id' => XX, 
));
```

## Known Issues


