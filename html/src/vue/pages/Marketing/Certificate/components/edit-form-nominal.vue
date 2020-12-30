<template>
    <div class="nominal-edit-form">
        <form @submit.prevent="onSubmit">
            <div class="form-group required">
                <label class="control-label" for="nominal_price">
                    Номинал (руб.)*
                </label>

                <input type="number"
                       class="form-control"
                       id="nominal_price"
                       v-model="price"
                       required
                       min="100"
                       placeholder="Введите номинал в рублях">
            </div>

            <div class="form-group required">
                <label class="control-label" for="nominal_amount">Кол-во ПС данного номинала (остаток) *</label>

                <input type="number"
                       class="form-control"
                       id="nominal_amount"
                       v-model="amount"
                       required
                       min="0"
                       placeholder="Введите количество">
            </div>

            <div class="form-group required">
                <label class="control-label" for="nominal_activation_period">Срок активации*</label>

                <input type="number"
                       class="form-control"
                       id="nominal_activation_period"
                       v-model="activation_period"
                       required
                       min="0"
                       placeholder="Введите срок в днях">

                <div class="mini_text">0 - безлимитный</div>
            </div>

            <div class="form-group required">
                <label class="control-label" for="nominal_validity">Период действия*</label>

                <input type="number"
                       class="form-control"
                       id="nominal_validity"
                       v-model="validity"
                       required
                       min="0"
                       placeholder="Введите срок в днях">

                <div class="mini_text">0 - безлимитный</div>
            </div>

            <div class="form-group required">
                <label class="control-label">Доступные дизайны*</label>

                <ul class="designs_list">

                    <li v-for="design in all_designs" :key="'nd-' + design.id">
                        <label :for="'design_item_' + design.id">
                            <input
                                type="checkbox"
                                :id="'design_item_' + design.id"
                                :checked="designs.indexOf(design.id) !== -1"
                                @change="onDesignChange(design.id)"
                            >
                            {{ design.name }}
                        </label>
                    </li>
                </ul>
            </div>
            <div class="form-group required">
                <label class="control-label" for="nominal_status">Активность</label><br>

                <div>
                    <input type="checkbox"
                           :checked="!!status"
                           @change="status=!status"
                           id="nominal_status">
                </div>
            </div>

            <button type="submit" class="btn btn-success">Сохранить</button>
            <button type="button" class="btn" @click="cancel">Отменить</button>

        </form>
    </div>
</template>

<script>
import Services from '../../../../../scripts/services/services';

export default {
    name: 'nominal-edit-form',
    props: {
        nominal: {
            type: Object,
            default: () => ({})
        },
        all_designs: {
            type: Array,
            default: () => ([])
        }
    },
    data() {
        return {
            price: '',
            status: '',
            activation_period: '',
            validity: '',
            amount: '',
            designs: []
        };
    },
    mounted() {
        if (this.nominal) {
            const nominal = this.nominal || {}

            this.price = nominal.hasOwnProperty('price') ? nominal.price : '';
            this.status = nominal.status || 0;
            this.activation_period = nominal.hasOwnProperty('activation_period') ? nominal.activation_period : '';
            this.validity = nominal.hasOwnProperty('validity') ? nominal.validity : '';
            this.amount = nominal.hasOwnProperty('amount') ? nominal.amount : '';
            (nominal.designs || []).forEach(design => {
                this.designs.push((design && design.hasOwnProperty('id')) ? design.id : design)
            })
        }
    },
    methods: {
        onDesignChange(id) {
            const index = this.designs.indexOf(id);
            (index === -1) ? this.designs.push(id) : this.designs.splice(index, 1);
        },
        onSubmit() {
            this.$emit('save', {
                price: this.price,
                status: this.status ? 1 : 0,
                activation_period: this.activation_period,
                validity: this.validity,
                amount: this.amount,
                designs: this.designs
            })
        },
        cancel() {
            this.$emit('cancel')
        }
    }
}
</script>

<style>
.nominal-edit-form .designs_list {
    list-style: none;
    margin-left: -25px;
}

.nominal-edit-form .mini_text {
    font-size: 14px;
    color: #929292;
}
</style>
