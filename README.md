# coop.symbiotic.primarycontact

![Screenshot](/images/screenshot.png)

The goal of this extension is to make it more easy to have one responsible contact for editing the organization and acting on its behalf.

In CiviCRM, if you use a form with on behalf option, a relation employee-employer is created. It is the same relationship type that is used when you or the contact add the employer. So there is no way to distinguish which was the person in charge of completing the form on behalf of the organization.

To solve this, we use a different relationship type :
*  this relationship is created automatically when a form with on behalf is completed
*  the relationship has permission over the organization by default
*  some convenient tokens are available to reach the primary contact of an organization or the on behalf organization of an individual

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

* PHP v5.4+
* CiviCRM 4.7+

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
git clone https://github.com/coopsymbiotic/coop.symbiotic.primarycontact.git
cv en primarycontact
```

## Usage

* go to Administer -> Customize Data and Screens -> Primary contact
* define the relationship type you want to use for the primary contact of an organization

## Available tokens

All the token below will work exactly the same if sent to the organization or the primary contact so you can send you email to both contacts. e.g. renew link will contains the cid and checksum of the primary contact even if you send it to the organization.

* `{primarycontact.first_name}` : Primary Contact: First name
* `{primarycontact.last_name}` : Primary Contact: Last name
* `{primarycontact.organization}` : Primary Contact: Organization
* `{primarycontact.organization_id}` : Primary Contact: Organization ID
* `{primarycontact.organization_checksum}` : Primary Contact: Organization Checksum
* `{primarycontact.primary_checksum}` : Primary Contact: Primary Contact Checksum
* `{primarycontact.primary_contact_id}` : Primary Contact: Primary Contact ID
* `{primarycontact.renewlink}` : Primary Contact: Renew link (add &id=XX)

The renew link will create a link that will work the same way for primary contact or organization so you can mass mail to any of them or both. You need to add the id of the contribution form you want to use by adding &id=XX after the token (replacing XX by the ID)


## Known Issues


