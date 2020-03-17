<template>
    <select>
        <slot></slot>
    </select>
</template>

<script>
import 'select2';
import 'select2/dist/css/select2.min.css';
import 'select2-bootstrap-theme/dist/select2-bootstrap.min.css';
import $ from 'jquery';

export default {
    name: 'v-select2',
    props: ['value', 'options', 'dropdownPosition'],
    mounted: function () {
        let vm = this;
        this.$nextTick(() => {
            let options = {theme: 'bootstrap',};
            if (this.dropdownPosition) {
                options.dropdownPosition = this.dropdownPosition; // below or above
            }
            $(this.$el).select2(options).val(this.value).trigger('change').on('select2:unselect', (e) => {
                vm.$emit('input', $(this.$el).val());
                vm.$emit('change');
            });
            $(this.$el).select2(options).val(this.value).trigger('change').on('select2:select', (e) => {
                vm.$emit('input', $(this.$el).val());
                vm.$emit('change');
            });
        });
    },
    watch: {
        value: function (value) {
            this.$nextTick(() => {
                $(this.$el).val(value).trigger('change');
            });
        },
        options: function (value) {
            this.$nextTick(() => {
                $(this.$el).val(this.value).trigger('change');
            });
        },
    },
    destroyed: function () {
        $(this.$el).off().select2('destroy')
    }
};
</script>
