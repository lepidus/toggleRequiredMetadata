<template>
	<!--
		Renders no visible content of its own: it exists so that the required
		mark on the affiliations label, a CSS sibling rule anchored on this
		element, is shown only when the plugin asks for it.
	-->
	<div class="trmAffiliationsRequired">
		<span class="-screenReader">{{ requiredLabel }}</span>
	</div>
</template>

<script setup>
defineProps({
	/** States the requirement for screen readers, which an asterisk does not. */
	requiredLabel: {type: String, required: true},
});
</script>
