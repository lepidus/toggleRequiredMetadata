# Toggle Required Metadata Plugin

This plugin lets a journal, press or server demand more from the contributors of a submission than the application does on its own: an **Affiliation**, a **Biography Statement** and an **ORCID iD** can each be made mandatory.

Each requirement is checked when the submission is completed, so no submission gets through without it. All three are also marked as required in the Contributor form, where the author fills them in.

The ORCID requirement is different in kind, because since 3.5 nobody types an ORCID iD: it is only recorded when the contributor authorises it through the verification e-mail. So what the plugin requires is that **at least one contributor has a verified ORCID iD** and that the **verification has been requested from all the others**. On the last step of the submission this is shown as a single block, counting how many contributors have verified and naming where each of them stands, so that the author can tell apart what is still up to them from what is only waiting on someone else. While any contributor is still unverified, the submission workflow shows a warning naming them.

## Compatibility

This plugin is compatible with the following PKP applications:

* OJS 3.5.0
* OPS 3.5.0
* OMP 3.5.0

Older application lines are supported by other branches of this plugin: `stable-3_3_0` (OJS 3.3) and `stable-3_4_0` (OJS, OPS and OMP 3.4).

## Installation

Install the **Toggle Required Metadata** plugin via the **Plugin Gallery** in **Dashboard > Website > Plugins**

## Settings

All three requirements are enabled by default. To change that:

1. After the plugin is installed, go to the plugin settings:
![plugin-settings-section](/assets/plugin-settings-section.png)

2. Select the metadata to be required. The ORCID option is only available while ORCID is enabled in the application's ORCID settings; when it is not, the option is shown disabled, along with a note explaining why:
![plugin-settings](/assets/plugin-settings.png)

3. The Contributor form then marks the selected metadata as required:
![plugin-in-action](/assets/application.png)

## Where each requirement is enforced

| Option | Contributor form | Finishing the submission |
|---|---|---|
| Affiliation | marked as required | blocked until every contributor has at least one affiliation |
| Biography Statement | marked as required; the form cannot be saved without it | blocked until every contributor has one |
| ORCID | marked as required beside the verification button; not editable by hand in 3.5 | blocked until one contributor has a verified iD and the verification has been requested from all the others, shown as one block with the standing of each contributor |

## Notes for 3.5

* **Contributors cannot be required to type an ORCID iD.** Since 3.5 the application refuses any ORCID iD sent from the Contributor form; an iD is only recorded through the ORCID verification flow. Earlier versions of this plugin required a typed iD whenever the ORCID Profile plugin was off, and that is no longer possible.
* **The ORCID option follows the application's ORCID settings.** With ORCID disabled the option takes no effect, and the saved choice is kept and applies again as soon as ORCID is enabled. Note that ORCID can also be enabled for the whole installation at the site level, which turns the requirement on for every journal, press or server at once.
* **The affiliation and the ORCID iD are marked in the Contributor form but checked when the submission is completed.** Both fields of 3.5 render their own label and ignore the one configured for them, so the plugin adds an element beside each to carry the required mark. Neither field is flagged as required to the form itself, because in both cases the form could then never be saved: adding an affiliation changes the field value without emitting a change event, and requesting the ORCID verification of a contributor who already exists sends the e-mail through a separate endpoint without touching the form at all. Either way, an error raised for the field would never be cleared.

## Building the front-end assets

The required marks on the affiliations and ORCID fields, the ORCID block on the last step of the submission and the workflow ORCID warning are Vue components, built with Vite. The build output is committed under `public/build/`, so installing the plugin needs no build step. To rebuild after changing anything under `resources/`:

```
npm install
npm run build
```

# License
__This plugin is licensed under the GNU General Public License v3.0__

__Copyright (c) 2022-2026 Lepidus Tecnologia__
