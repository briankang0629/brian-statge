<template>
    <section class="wrapper">
        <!-- Header -->
        <Header/>
        <!-- Menu -->
        <Menu/>

        <!-- Main -->
        <router-view/>

        <!-- Footer -->
        <Footer/>
    </section>
</template>

<script>
    export default {
        components: {
            Header: () => import('@/components/common/header.vue'),
            Menu: () => import('@/components/common/menu.vue'),
            Footer: () => import('@/components/common/footer.vue')
        },
        mounted() {
            //側邊欄最小化
            let el = document.querySelector('body');
            el.setAttribute('class','sidebar-mini');
        },
        method: {}
    }
</script>

<style>
    .modal-backdrop {
        opacity: 0.5 !important;
    }

    .container-fluid .card-table {
        min-height: 500px;
    }

    /**
     * 網頁版彈窗
     */
    @media (min-width: 768px) {
        .modal-content {
            min-width: 600px !important;
            margin-left: -50px !important;
        }
    }


    /**
     * 搜尋無資料字樣
     */
    .text-notfound {
        width: 100%;
        margin: 10% 0 ;
    }

    /**
     * 側邊選單字體大小
     */
    .sidebar ul li.nav-item p {
        font-size: 14px;
    }
</style>