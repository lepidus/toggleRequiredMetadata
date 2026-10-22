# Toggle Required Metadata Plugin

**English** · [Português (Brasil)](README.pt_BR.md) · [Español](README.es.md)

This plugin lets a journal, press or server demand more from the contributors of a submission than the application does on its own: an **Affiliation**, a **Biography Statement** and an **ORCID iD** can each be made mandatory.

Each requirement is marked in the Contributor form, where the author fills it in, and checked again when the submission is completed, so no submission is received without it.

The ORCID requirement depends on the [ORCID Profile plugin](https://github.com/pkp/orcidProfile). While it is enabled, an iD is not typed but authorised by the contributor, so what this plugin requires is that **at least one contributor has an authenticated ORCID iD** and that the **authorisation has been requested from all the others**. While it is disabled, an iD is typed in the Contributor form, and every contributor has to have one.

## Compatibility

This plugin is compatible with the following PKP applications:

* OJS 3.4.0
* OPS 3.4.0
* OMP 3.4.0

Other application lines are supported by other branches of this plugin: `stable-3_3_0` (OJS, OPS and OMP 3.3) and `main` (OJS, OPS and OMP 3.5).

## Installation

Install the **Toggle Required Metadata** plugin via the **Plugin Gallery** in **Dashboard > Website > Plugins**

## Settings

Enabling the plugin requires nothing on its own: every requirement is off until it is chosen. The plugin settings, reached from **Dashboard > Website > Plugins**, are where you pick which of them a submission has to meet:

![The plugin settings section](/assets/plugin-settings-section.png)

![The plugin settings with the requirements enabled](/assets/plugin-settings.png)

## Where each requirement is enforced

| Option | Contributor form | Finishing the submission |
|---|---|---|
| Affiliation | marked as required | blocked until every contributor has one |
| Biography Statement | marked as required | blocked until every contributor has one |
| ORCID | marked as required only while the ORCID Profile plugin is disabled, since an authorised iD cannot be typed | blocked until every contributor has an iD, or, with the ORCID Profile plugin enabled, until one contributor has authenticated theirs and the authorisation has been requested from the others |

### In the Contributor form

The selected metadata is marked as required where the author fills it in, and the form cannot be saved without it:

![The Contributor form with the required metadata](/assets/application.png)

### When the submission is completed

Every requirement is checked again on the last step, and the submission cannot be sent until all of them are met. The warnings are shown at the top of the contributors panel, next to the Edit button that resolves them.

### What the check does not cover

The requirements are about what comes in through the submission form, and that is where they are enforced: they are checked when an author completes a submission, and nowhere else. A contributor edited later in the workflow, a submission created through the REST API, and anything brought in by an import or by QuickSubmit never go through the check, and neither does publication. What the plugin holds is the door: no submission is *received* without the metadata, which is not the same as no submission in the journal ever lacking it.

## While the submission is in the workflow

Once the submission is in, the ORCID requirement can still be unmet. While it is, and the submission is still in progress, the workflow says who is holding it up, telling apart the contributors nobody asked for the authorisation yet from the ones who were sent the e-mail and have not opened it, with the advice that fits the reader — an editor can request the authorisation or send it again, an author can mostly only chase the contributor. The warning is not shown on a submission that is no longer in progress — declined, scheduled or published, which includes the new versions of a published one and everything published before the plugin was enabled — nor once one contributor has authenticated their iD and the authorisation was requested from all the others.

# License
__This plugin is licensed under the GNU General Public License v3.0__

__Copyright (c) 2022-2026 Lepidus Tecnologia__
