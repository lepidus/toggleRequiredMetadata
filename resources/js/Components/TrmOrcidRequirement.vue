<template>
	<!--
		One block for a requirement that has two halves, so that it reads as a
		single thing to settle: the rule once, how far the contributors are from
		it, and where each of them stands.
	-->
	<li v-if="requirement" class="trmOrcidRequirement">
		<PkpNotification type="warning">
			<h4 class="trmOrcidRequirement__summary">
				<PkpIcon icon="Error" class="h-5 w-5" :inline="true" />
				{{ requirement.summary }}
			</h4>
			<p class="trmOrcidRequirement__rule">{{ requirement.rule }}</p>
			<ul class="trmOrcidRequirement__contributors">
				<li
					v-for="(contributor, index) in requirement.contributors"
					:key="index"
					:class="`trmOrcidRequirement__contributor--${contributor.status}`"
				>
					<PkpIcon
						:icon="statusIcons[contributor.status]"
						class="h-5 w-5"
						:inline="true"
					/>
					<span class="trmOrcidRequirement__name">{{ contributor.name }}</span>
					<span class="trmOrcidRequirement__note">{{ contributor.note }}</span>
				</li>
			</ul>
		</PkpNotification>
	</li>
</template>

<script setup>
defineProps({
	/**
	 * What the requirement is still waiting for, as the submission validation
	 * describes it, or undefined once it is met. Comes from the wizard's own
	 * errors, so it follows every revalidation.
	 */
	requirement: {type: Object, default: null},
});

// Telling "waiting on someone else" apart from "still up to you" is the whole
// point of the block, so each state carries its own icon. A verified iD gets
// the ORCID mark itself, which is what the contributor sees everywhere else.
const statusIcons = {
	verified: 'Orcid',
	awaitingVerification: 'Clock',
	declined: 'Error',
	noPendingLink: 'Error',
	notRequested: 'Error',
	unverifiedOrcid: 'Error',
};
</script>
