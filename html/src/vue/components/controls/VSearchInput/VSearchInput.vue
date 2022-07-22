<template>
    <div class="position-relative">
        <input class="form-control" type="text" placeholder="Поиск..."
               @input="onInput($event.target.value)"
               :value="value"
        >
        <button class="input-close-btn btn small-btn badge badge-danger"
                @click="onInput(null)">X</button>
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
        top: calc(50% - 10px);
    }
</style>