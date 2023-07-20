<template>
	<tr>
		<th scope="col">
			<span class="hidden-visually">
				{{ t('my_company', 'File') }}
			</span>
		</th>
		<th scope="col">
			<span>{{ t('my_company', 'Date') }}</span>
		</th>
		<th scope="col">
			<span>{{ t('my_company', 'Value') }}</span>
		</th>
	</tr>
</template>

<script>
export default {
	name: 'PaymentsListHeader',
}
</script>
