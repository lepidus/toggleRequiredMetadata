# Toggle Required Metadata Plugin

**English** · [Português (Brasil)](README.pt_BR.md) · [Español](README.es.md)

This plugin lets a journal, press or server demand more from the contributors of a submission than the application does on its own: an **Affiliation**, a **Biography Statement** and an **ORCID iD** can each be made mandatory.

Each requirement is marked in the Contributor form, where the author fills it in, and checked again on the third step of the submission, so no submission is received without it.

The ORCID requirement depends on the [ORCID Profile plugin](https://github.com/pkp/orcidProfile). While it is enabled, an iD is not typed but authorised by the contributor, so what this plugin requires is that **at least one contributor has an authenticated ORCID iD** and that the **authorisation has been requested from all the others**. While it is disabled, an iD is typed in the Contributor form, and every contributor has to have one.

## Compatibility

The latest release of this plugin is compatible with the following PKP applications:

* OJS 3.3.0
* OPS 3.3.0
* OMP 3.3.0

Newer application lines are supported by other branches of this plugin: `stable-3_4_0` (OJS, OPS and OMP 3.4) and `main` (OJS, OPS and OMP 3.5).

## Installation

Install the **Toggle Required Metadata** plugin via the **Plugin Gallery** in **Dashboard > Website > Plugins**

## Settings

Enabling the plugin requires nothing on its own: every requirement is off until it is chosen. The plugin settings are where you pick which of them a submission has to meet:

![The plugin settings section](/assets/plugin-settings-section.png)

![The plugin settings with the requirements enabled](/assets/plugin-settings.png)

## Where each requirement is enforced

| Option | Contributor form | Third step of the submission |
|---|---|---|
| Affiliation | marked as required | blocked until every contributor has one |
| Biography Statement | marked as required, and the form cannot be saved without it | blocked until every contributor has one |
| ORCID | marked as required only while the ORCID Profile plugin is disabled, since an authorised iD cannot be typed | blocked until every contributor has an iD, or, with the ORCID Profile plugin enabled, until one contributor has authenticated theirs and the authorisation has been requested from the others |

### In the Contributor form

The selected metadata is marked as required where the author fills it in:

![The Contributor form with the required metadata](/assets/application.png)

### On the third step of the submission

Every requirement is checked again before the submission moves on, and what is missing is listed right below the contributors, so the author can tell what is left to fill in. OMP takes that place for the chapters, so on a press the list comes at the end of the metadata, just above the button that saves the step.

### What the check does not cover

The requirements are about what comes in through the submission form, and that is where they are enforced: they are checked on the third step of a submission, and nowhere else. A contributor edited later in the workflow, a submission created through the REST API, and anything brought in by an import or by QuickSubmit never go through the check, and neither does publication. What the plugin holds is the door: no submission is *received* without the metadata, which is not the same as no submission in the journal ever lacking it.

## While the submission is in the workflow

Once the submission is in, the ORCID requirement can still be waiting on a contributor who has not authorised their iD. The workflow says who, with the advice that fits the reader — an editor can ask for the authorisation again, an author can only chase the contributor.

# License
__This plugin is licensed under the GNU General Public License v3.0__

__Copyright (c) 2022-2026 Lepidus Tecnologia__
