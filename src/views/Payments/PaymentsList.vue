<template>
	<Fragment>
		<NcEmptyContent v-if="list.length === 0"
			:title="t('my_company', 'No payments')">
			<template #icon>
				<NcIconSvgWrapper :svg="iconPaymentsRaw" />
			</template>
		</NcEmptyContent>
		<RecycleScroller v-else
			class="scroller"
			:items="list"
			key-field="uuid"
			list-tag="tbody"
			role="table">
			<template #before>
				<PaymentsListHeader />
			</template>
			<template #default="{ item, index }">
				<PaymentListRow
					:item="item"
					:index="index" />
			</template>
		</RecycleScroller>
	</Fragment>
</template>

<script>
import { Fragment } from 'vue-frag'
import { RecycleScroller } from 'vue-virtual-scroller'
import NcEmptyContent from '@nextcloud/vue/dist/Components/NcEmptyContent.js'
import NcIconSvgWrapper from '@nextcloud/vue/dist/Components/NcIconSvgWrapper.js'
import PaymentsListHeader from './PaymentsListHeader.vue'
import PaymentListRow from './PaymentListRow.vue'
import iconPaymentsRaw from '../../../img/payments.svg?raw'

export default {
	name: 'PaymentsList',
	components: {
		Fragment,
		RecycleScroller,
		NcEmptyContent,
		NcIconSvgWrapper,
		PaymentsListHeader,
		PaymentListRow,
	},
	props: {
		list: {
			type: Array,
			default: () => [
				{
					uuid: 'cb91cea7-5ce3-45c3-8879-fdd96b86006a',
					value: 2000.00,
					date: '2023-03-30',
				},
				{
					uuid: 'cb91cea7-5ce3-45c3-8879-fdd96b86006a',
					value: 1000.00,
					date: '2023-01-30',
				},
			],
		}
	},
	data() {
		return {
			iconPaymentsRaw,
		}
	},
}
</script>

<style lang="scss" scoped>
.scroller {
	height: 100%;
}
.empty {
	:deep {
		.icon-vue {
			width: 64px;
			height: 64px;

			svg {
				max-width: 64px;
				max-height: 64px;
			}
		}
	}
}
</style>
