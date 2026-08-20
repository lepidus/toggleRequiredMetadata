# Toggle Required Metadata Plugin

This plugin makes it possible to make the **Affiliation**, **ORCID** and **Biography Statement** fields required for the submissions authors to fill in.

These fields belong to the Contributor of the Submission.

When ORCID is enabled in the application's ORCID settings, this plugin requires at least one of the contributors to have a verified ORCID iD, and the ORCID verification to have been requested from the remaining ones, in order to finish a submission. The workflow shows a warning naming the contributors whose ORCID iD is not verified yet.

## Compatibility

This plugin is compatible with the following PKP applications:

* OJS 3.5.0
* OPS 3.5.0
* OMP 3.5.0

Older application lines are supported by other branches of this plugin: `stable-3_3_0` (OJS 3.3) and `stable-3_4_0` (OJS, OPS and OMP 3.4).

## Installation

Install the **Toggle Required Metadata** plugin via the **Plugin Gallery** in **Dashboard > Website > Plugins**

## Settings

By default, both fields are made required. But it is possible to set it different:

1. After the plugin is installed, go to the plugin settings:
![plugin-settings-section](/assets/plugin-settings-section.png)

2. Select the metadata to be required:
![plugin-settings](/assets/plugin-settings.png)

3. The selected metadata are going to be required fields in the Submission's Contributor form:
![plugin-in-action](/assets/application.png)

## What changed for 3.5

* **Contributors cannot be required to type an ORCID iD.** Since 3.5 the application refuses any ORCID iD sent from the contributor form: an iD is only recorded through the ORCID verification flow. Earlier versions of this plugin required a typed iD whenever the ORCID Profile plugin was off, and that is no longer possible. What the ORCID option requires is one verified ORCID iD among the contributors, plus a verification request sent to the others.
* **The ORCID option follows the application's ORCID settings.** With ORCID disabled, the option is shown disabled and takes no effect; the saved choice is kept and applies again as soon as ORCID is enabled. Note that ORCID can also be enabled for the whole installation at the site level, which turns the requirement on for every journal, press or server at once.
* **The affiliation is marked in the contributor form, but checked when the submission is completed.** The affiliations field of 3.5 renders its own label and ignores the one configured for it, so the plugin adds an element next to it to carry the required mark. The field is not flagged as required to the form itself: adding an affiliation mutates the field value without emitting a change event, so an error raised for that field is never cleared and the contributor form can no longer be saved, even after the affiliation is filled in.

## Building the front-end assets

The contributor form mark and the workflow ORCID warning are Vue components, built with Vite. The build output is committed under `public/build/`, so a plain installation of the plugin needs no build step. To rebuild after changing anything under `resources/`:

```
npm install
npm run build
```

# License
__This plugin is licensed under the GNU General Public License v3.0__

__Copyright (c) 2022-2024 Lepidus Tecnologia__
