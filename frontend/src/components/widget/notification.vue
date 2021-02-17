<template>
    <notifications
        position="center top"
    >
    </notifications>
</template>

<style lang="scss">


.vue-notification-wrapper {
    .vue-notification {
        min-height: 58px;
        margin: 10px;
        margin-bottom: 0;
        border-radius: 3px;
        font-size: 13px;
        padding: 10px 20px;

        &.warn {
            /*border: 1px solid #f48a06;*/
        }
        &.primary {
            /*border: 1px solid #187fe7;*/
        }
        &.error {
            /*border: 1px solid #b82e24;*/
        }
        &.success {
            padding-top: 20px;
            /*border: 1px solid #42a85f;*/
        }
        .notification-title {}
        .notification-content {}
    }

}

</style>
