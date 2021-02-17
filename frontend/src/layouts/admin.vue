<template>
    <section class="wrapper">

        <!-- header -->
        <Header/>

        <!-- menu -->
        <Menu/>

        <!-- main -->
        <router-view/>

        <!-- footer -->
        <Footer/>

    </section>
</template>

<script>
    export default {
        components: {
            Header: () => import('@/components/common/header.vue'),
            Menu: () => import('@/components/common/menu.vue'),
            Footer: () => import('@/components/common/footer.vue'),
        },
        mounted() {
            //側邊欄最小化
            let el = document.querySelector('body');
            el.setAttribute('class','sidebar-mini');
        },
        method: {}
    }
</script>