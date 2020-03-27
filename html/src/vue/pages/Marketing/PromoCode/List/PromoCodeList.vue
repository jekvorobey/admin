<template>
    <layout-main>
        <div class="row mb-3">
            <div class="col-12 mt-3">
                <a :href="getRoute('promo-code.create')" class="btn btn-success">Создать промокод</a>
            </div>
        </div>
    </layout-main>
</template>

<script>
    export default {
        name: 'page-promo-code-list',
        components: {
        },
        props: {
        },
    }
</script>
