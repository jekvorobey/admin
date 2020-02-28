<template>
    <ul class="list-unstyled">
        <li v-for="category in sortCategories">
            <b-form-checkbox v-model="form" :value="category.id" :disabled="!!disabled">{{ category.name }}</b-form-checkbox>
            <modal-categories-checkbox
                v-if="category.children.length"
                :categories="category.children"
                :model.sync="form"
                :disabled="childrenDisabled(category.id)"
                class="pl-4"
            />
        </li>
    </ul>
</template>

<script>
export default {
    name: 'modal-categories-checkbox',
    props: ['categories', 'model', 'disabled'],
    data() {
        return {

        }
    },
    watch: {
        'disabled': function (val, oldVal) {
            if (!!this.disabled) {
                this.categories.forEach(category => {
                    let key = this.form.indexOf(category.id);
                    if (key > -1) {
                        this.$delete(this.form, key);
                    }
                });
            }
        }
    },
    computed: {
        form: {
            get() {return this.model;},
            set(value) {this.$emit('update:model', value);},
        },
        sortCategories() {
            return this.categories.sort((a,b) => (a.name > b.name) ? 1 : ((b.name > a.name) ? -1 : 0));
        },
    },
    methods: {
        childrenDisabled(category_id) {
            return !!this.disabled || this.form.indexOf(category_id) > -1;
        },
    }
};
</script>

<style scoped>

</style>