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
	submission: {type: Object, default: null},
});

const statusIcons = {
	notRequested: 'Error',
	unverifiedOrcid: 'Error',
	declined: 'Error',
	awaitingVerification: 'Clock',
};

const authors = computed(() => props.publication?.authors ?? []);

const isRequirementMet = computed(
	() =>
		authors.value.some(
			(author) => author.orcidVerificationStatus === 'verified',
		) &&
		authors.value.every(
			(author) =>
				!['notRequested', 'unverifiedOrcid'].includes(
					author.orcidVerificationStatus,
				),
		),
);

const namesByStatus = computed(() => {
	const names = Object.fromEntries(
		Object.keys(statusIcons).map((status) => [status, []]),
	);

	authors.value.forEach((author) => {
		const status = author.orcidVerificationStatus;
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

const isShown = computed(
	() =>
		pendingStatuses.value.length > 0 &&
		props.submission?.status === pkp.const.STATUS_QUEUED &&
		!isRequirementMet.value,
);

function describe(status) {
	return props.messages[status].replace('{$pendingAuthors}', () =>
		namesByStatus.value[status].join(', '),
	);
}
</script>
