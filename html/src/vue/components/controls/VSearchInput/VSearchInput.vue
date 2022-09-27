<template>
    <div>
        <input class="fixed-input-search form-control search-input" type="text" placeholder="Поиск..."
               @input="onInput($event.target.value)"
               :value="value"
        >
        <button class="search-input-close-btn input-close-btn btn small-btn badge badge-danger"
                @click="onInput('')">X</button>
    </div>
</template>

<script>
    export default {
        name: "v-search-input",
        emits: ['onSearch'],
        props:{
            value:{
                type: String,
                required: true
            }
        },
        methods: {
            onInput(value){
                this.$emit('onSearch', value)
            }
        }
    }
</script>

<style scoped>
    .input-close-btn{
        position: absolute;
        right: 10px;
        top: 8px;
    }
    .fixed-input-search{
        width: 100%;
        max-width: inherit;
    }
</style>