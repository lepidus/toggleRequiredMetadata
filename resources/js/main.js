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

/**
 * The contributors tab of the workflow, which is where the ORCID warning
 * belongs for an editor: they work through every stage of every submission, and
 * a warning about contributors would otherwise follow them the whole way.
 */
function isContributorsMenu(menuState) {
	return (
		menuState?.primaryMenuItem === 'publication' &&
		menuState?.secondaryMenuItem === 'contributors'
	);
}

pkp.registry.storeExtendFn('workflow', 'getPrimaryItems', (items, args) => {
	const config =
		args.pageInitConfig?.componentForms?.toggleRequiredMetadataOrcidWarning;

	if (!config || !args.selectedPublication) {
		return items;
	}

	// `getPrimaryItems` is not given the dashboard page directly, but the page
	// config it does receive carries it.
	const isEditor =
		args.pageInitConfig.dashboardPage === 'editorialDashboard';

	// An author only ever opens their own submission, and the warning is the
	// one thing they may still have to act on, so theirs stays where it is.
	if (isEditor && !isContributorsMenu(args.selectedMenuState)) {
		return items;
	}

	return [
		{
			component: 'TrmOrcidWarning',
			props: {
				messages: isEditor ? config.editorMessages : config.authorMessages,
				publication: args.selectedPublication,
			},
		},
		...items,
	];
});
