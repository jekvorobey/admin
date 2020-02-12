<template>
    <b-input-group>
        <b-form-input :placeholder="placeholder" v-model="newValue">
        </b-form-input>
        <b-input-group-append>
            <b-button variant="info" @click="onClick">Добавить</b-button>
        </b-input-group-append>
    </b-input-group>
</template>

<script>
    export default {
        props: {
            placeholder: String,
        },
        data() {
            return {
                newValue: '',
            };
        },
        methods: {
            onClick() {
                this.$emit('add', this.newValue);
                this.newValue = '';
            },
        },
    }
</script>
