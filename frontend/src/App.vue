<template>
    <div>
        <!-- notification -->
        <notification/>

        <router-view/>
    </div>
</template>

<script>
	export default {
		components: {
			notification: () => import('@/components/widget/notification.vue')
		},
	}
</script>