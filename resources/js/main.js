import '../css/requiredMark.css';
import '../css/orcidRequirement.css';

import TrmRequiredMark from './Components/TrmRequiredMark.vue';
import TrmOrcidRequirement from './Components/TrmOrcidRequirement.vue';
import TrmOrcidWarning from './Components/TrmOrcidWarning.vue';

// Registered under the name the form config asks for, so the contributor form
// renders it like any other field.
pkp.registry.registerComponent('trm-required-mark', TrmRequiredMark);
// Rendered as <trm-orcid-requirement> from the review step of the wizard, which
// Vue resolves to the name registered here.
pkp.registry.registerComponent('TrmOrcidRequirement', TrmOrcidRequirement);
pkp.registry.registerComponent('TrmOrcidWarning', TrmOrcidWarning);

pkp.registry.storeExtendFn('workflow', 'getPrimaryItems', (items, args) => {
	const config =
		args.pageInitConfig?.componentForms?.toggleRequiredMetadataOrcidWarning;

	if (!config || !args.selectedPublication) {
		return items;
	}

	// `getPrimaryItems` is not given the dashboard page directly, but the page
	// config it does receive carries it.
	const message =
		args.pageInitConfig.dashboardPage === 'editorialDashboard'
			? config.editorMessage
			: config.authorMessage;

	return [
		{
			component: 'TrmOrcidWarning',
			props: {message, publication: args.selectedPublication},
		},
		...items,
	];
});
