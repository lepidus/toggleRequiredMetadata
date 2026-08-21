<template>
	<PkpNotification v-if="pendingNames.length" type="warning">
		<!-- The same clock the review step uses for a verification already asked
		     for and not yet given, so both surfaces read alike. -->
		<PkpIcon icon="Clock" class="h-5 w-5" :inline="true" />
		{{ warning }}
	</PkpNotification>
</template>

<script setup>
import {computed} from 'vue';

const props = defineProps({
	/**
	 * Message naming the contributors still without a verified ORCID iD.
	 * Carries a `{$pendingAuthors}` placeholder, filled in here so the warning
	 * follows the contributor list without a page reload.
	 */
	message: {type: String, required: true},
	/** The publication currently selected in the workflow. */
	publication: {type: Object, default: null},
});

// Whether the verification was already requested cannot be told apart here:
// the publication maps its authors with the summary properties only, and
// `orcidVerificationRequested` is not one of them. It hardly matters, since a
// submission cannot be completed until the verification has been requested
// from every contributor.
const pendingNames = computed(() =>
	(props.publication?.authors ?? [])
		.filter((author) => !author.orcidIsVerified)
		.map((author) => author.fullName)
		.filter(Boolean),
);

const warning = computed(() =>
	props.message.replace('{$pendingAuthors}', pendingNames.value.join(', ')),
);
</script>
