# Toggle Required Metadata Plugin

**English** · [Português (Brasil)](README.pt_BR.md) · [Español](README.es.md)

This plugin lets a journal, press or server demand more from the contributors of a submission than the application does on its own: an **Affiliation**, a **Biography Statement** and an **ORCID iD** can each be made mandatory.

Each requirement is checked when the submission is completed, so no submission gets through without it. All three are also marked as required in the Contributor form, where the author fills them in.

The ORCID requirement is different in kind, because since 3.5 nobody types an ORCID iD: it is only recorded when the contributor authorises it through the verification e-mail. So what the plugin requires is that **at least one contributor has a verified ORCID iD** and that the **verification has been requested from all the others**. On the last step of the submission this is shown as a single block, counting how many contributors have verified and naming where each of them stands, so that the author can tell apart what is still up to them from what is only waiting on someone else.

![The ORCID requirement on the last step of a submission](/assets/review-orcid-requirement.png)

## Compatibility

This plugin is compatible with the following PKP applications:

* OJS 3.5.0
* OPS 3.5.0
* OMP 3.5.0

Older application lines are supported by other branches of this plugin: `stable-3_3_0` (OJS, OPS and OMP 3.3) and `stable-3_4_0` (OJS, OPS and OMP 3.4).

## Installation

Install the **Toggle Required Metadata** plugin via the **Plugin Gallery** in **Dashboard > Website > Plugins**

## Settings

Enabling the plugin requires nothing on its own: every requirement is off until it is chosen. The plugin settings, reached from **Dashboard > Website > Plugins**, are where you pick which of them a submission has to meet:

![The plugin settings with the three requirements enabled](/assets/settings-orcid-enabled.png)

The ORCID option is only available while ORCID is enabled in the application's ORCID settings. When it is not, the option is shown disabled, along with a note explaining why, and the saved choice is left untouched:

![The plugin settings with ORCID not enabled in the application](/assets/settings-orcid-disabled.png)

## Where each requirement is enforced

| Option | Contributor form | Finishing the submission |
|---|---|---|
| Affiliation | marked as required | blocked until every contributor has at least one affiliation |
| Biography Statement | marked as required; the form cannot be saved without it | blocked until every contributor has one |
| ORCID | marked as required beside the verification button; not editable by hand in 3.5 | blocked until one contributor has a verified iD and the verification has been requested from all the others, shown as one block with the standing of each contributor |

### In the Contributor form

The selected metadata is marked as required where the author fills it in. The ORCID iD is marked beside the verification button, which is the only way a contributor can supply one in 3.5:

![The ORCID field of the Contributor form, marked as required](/assets/contributor-form-orcid-required.png)

![The same field once the verification has been requested](/assets/contributor-form-orcid-requested.png)

### When the submission is completed

Every requirement is checked again on the last step, and the submission cannot be sent until all of them are met. The warnings are grouped at the top of the contributors panel, next to the Edit button that resolves them:

![The last step of a submission with all three requirements unmet](/assets/review-all-requirements.png)

### What the check does not cover

The requirements are about what comes in through the submission form, and that is where they are enforced: they are checked when an author completes a submission, and nowhere else. A contributor edited later in the workflow, a submission created through the REST API, and anything brought in by an import or by QuickSubmit never go through the check, and neither does publication. What the plugin holds is the door: no submission is *received* without the metadata, which is not the same as no submission in the journal ever lacking it.

## While the submission is in the workflow

Once the submission is in, the ORCID requirement can still be unmet. While it is, and the publication is not published, the contributors tab of the workflow says who is holding it up, telling apart the contributors nobody asked yet, the ones who were sent the verification e-mail and have not opened it, the ones who declined the authorization in ORCID and the ones left without a pending verification link, with the advice that fits the reader — an editor can request or resend the verification, an author can mostly only chase the contributor. The warning is not shown on a published publication, which includes everything published before the plugin was enabled, nor once one contributor has verified their iD and the verification was requested from all the others:

![The ORCID warning as an editor sees it](/assets/workflow-warning-editor.png)

![The ORCID warning as an author sees it](/assets/workflow-warning-author.png)

## Notes for 3.5

* **Contributors cannot be required to type an ORCID iD.** Since 3.5 the application refuses any ORCID iD sent from the Contributor form; an iD is only recorded through the ORCID verification flow. Earlier versions of this plugin required a typed iD whenever the ORCID Profile plugin was off, and that is no longer possible.
* **The ORCID option follows the application's ORCID settings.** With ORCID disabled the option takes no effect, and the saved choice is kept and applies again as soon as ORCID is enabled. Note that ORCID can also be enabled for the whole installation at the site level, which turns the requirement on for every journal, press or server at once.
* **The affiliation and the ORCID iD are marked in the Contributor form but checked when the submission is completed.** Both fields render their own label, so the plugin adds an element beside each to carry the required mark. Neither is flagged as required to the form itself: neither one clears an error once raised, which would leave the Contributor form impossible to save.

## Building the front-end assets

The required marks on the affiliations and ORCID fields, the ORCID block on the last step of the submission and the workflow ORCID warning are Vue components, built with Vite. The build output is committed under `public/build/`, so installing the plugin needs no build step. To rebuild after changing anything under `resources/`:

```
npm install
npm run build
```

# License
__This plugin is licensed under the GNU General Public License v3.0__

__Copyright (c) 2022-2026 Lepidus Tecnologia__
