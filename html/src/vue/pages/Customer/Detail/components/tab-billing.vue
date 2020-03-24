<template>
    <div>
        <table class="table">
        <thead>
            <tr>
                <th>ID заказа/транзакции</th>
                <th>Дата</th>
                <th>Сумма</th>
                <th>Операция</th>
            </tr>
        </thead>
        <tbody>
            <tr class="table-info">
                <td colspan="2">Всего</td>
                <td colspan="2">{{ customer.referral_bill }}</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td>
                    <select class="form-control form-control-sm" v-model="filter.type">
                        <option :value="null">-</option>
                        <option v-for="(type, id) in types" :value="id">{{ type }}</option>
                    </select>
                </td>
            </tr>
            <tr v-for="operation in filterOperations">
                <td>{{ operation.action_id }}</td>
                <td>{{ operation.created_at }}</td>
                <td>{{ operation.value }}</td>
                <td>{{ operation.type_name }}</td>
            </tr>
        </tbody>
        </table>
    </div>
</template>

<script>
import Services from '../../../../../scripts/services/services.js';

export default {
    name: 'tab-billing',
    props: ['model'],
    data() {
        return {
            operations: [],
            types: {},
            filter: {
                type: null,
            }
        }
    },
    computed: {
        customer: {
            get() {return this.model;},
            set(value) {this.$emit('update:model', value);}
        },
        filterOperations() {
            return this.operations.filter(operation => {
                if (this.filter.type) {
                    if (operation.type !== Number(this.filter.type)) {
                        return false;
                    }
                }

                return true;
            });
        },
    },
    created() {
        Services.showLoader();
        Services.net().get(this.getRoute('customers.detail.billing', {id: this.model.id})).then(data => {
            this.operations = data.operations;
            this.types = data.types;
        }).finally(() => {
            Services.hideLoader();
        })
    }
};
</script>

<style scoped>

</style>