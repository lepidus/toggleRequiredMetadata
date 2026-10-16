<template>
	<PkpNotification v-if="isShown" type="warning">
		<p
			v-for="status in pendingStatuses"
			:key="status"
			:class="`trmOrcidWarning__line trmOrcidWarning__line--${status}`"
		>
			<PkpIcon :icon="statusIcons[status]" class="h-5 w-5" :inline="true" />
			{{ describe(status) }}
		</p>
	</PkpNotification>
</template>

<script setup>
import {computed} from 'vue';

const props = defineProps({
	messages: {type: Object, required: true},
	/** The publication currently selected in the workflow. */
	publication: {type: Object, default: null},
});

const statusIcons = {
	notRequested: 'Error',
	noPendingLink: 'Error',
	declined: 'Error',
	awaitingVerification: 'Clock',
};

function hasVerifiedOrcid(author) {
	return !!author.orcid && !!author.orcidIsVerified;
}

function orcidStatus(author) {
	if (hasVerifiedOrcid(author)) {
		return 'verified';
	}

	if (!author.orcidVerificationRequested) {
		return 'notRequested';
	}

	if (author.orcidEmailToken) {
		return 'awaitingVerification';
	}

	return author.orcidAccessDenied ? 'declined' : 'noPendingLink';
}

const authors = computed(() => props.publication?.authors ?? []);

const isRequirementMet = computed(
	() =>
		authors.value.some(hasVerifiedOrcid) &&
		authors.value.every((author) => orcidStatus(author) !== 'notRequested'),
);

const isShown = computed(
	() =>
		authors.value.length > 0 &&
		props.publication?.status !== pkp.const.STATUS_PUBLISHED &&
		!isRequirementMet.value,
);

const namesByStatus = computed(() => {
	const names = Object.fromEntries(
		Object.keys(statusIcons).map((status) => [status, []]),
	);

	authors.value.forEach((author) => {
		const status = orcidStatus(author);
		if (names[status] && author.fullName) {
			names[status].push(author.fullName);
		}
	});

	return names;
});

const pendingStatuses = computed(() =>
	Object.keys(statusIcons).filter(
		(status) => namesByStatus.value[status].length,
	),
);

function describe(status) {
	return props.messages[status].replace(
		'{$pendingAuthors}',
		namesByStatus.value[status].join(', '),
	);
}
</script>
