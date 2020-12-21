<template>
	<div class="content-wrapper">
		<breadcrumb/>
		<router-view></router-view>
	</div>
</template>

<script>
	export default {
		layout: 'admin',
		components: {
			breadcrumb: () => import('@/components/common/breadcrumb.vue'),
		},
	}
</script>