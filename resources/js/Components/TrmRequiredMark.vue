<template>
	<!--
		Renders no visible content of its own: it exists so that the required
		mark on the label of the field that follows, a CSS sibling rule anchored
		on this element, is shown only when the plugin asks for it.
	-->
	<div class="trmRequiredMark" :class="`trmRequiredMark--${markedField}`">
		<span class="-screenReader">{{ requiredLabel }}</span>
	</div>
</template>

<script setup>
defineProps({
	/** Name of the field being marked, used as a CSS modifier. */
	markedField: {type: String, required: true},
	/** States the requirement for screen readers, which an asterisk does not. */
	requiredLabel: {type: String, required: true},
});
</script>
