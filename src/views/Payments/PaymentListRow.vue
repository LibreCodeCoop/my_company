<template>
	<Fragment :data-index="index">
		<td>
			<span>{{ item.uuid }}</span>
		</td>
		<td>
			<span>{{ item.value }}</span>
		</td>
		<td>
			<span>{{ item.date }}</span>
		</td>
	</Fragment>
</template>

<script>
import { Fragment } from 'vue-frag'

export default {
	name: 'PaymentListRow',

	components: {
		Fragment,
	},

	props: {
		item: {
			type: Object,
			required: true,
		},
		index: {
			type: Number,
			required: true,
		},
	},
}
</script>
