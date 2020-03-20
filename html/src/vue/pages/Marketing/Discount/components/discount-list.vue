<template>
    <div class="col-12" style="max-height:300px;overflow:auto;padding:10px;border:1px solid #ccc">
        <p v-for="discount in discounts">
            #{{ discount.id }} – {{ discount.name }}
        </p>
    </div>
</template>

<script>
    export default {
        props: {
            discounts: Array,
        },
    }
</script>
