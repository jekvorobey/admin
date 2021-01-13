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
                <div class="mini_text">Номинал должен быть целым числом не менее 100 рублей</div>
            </div>

            <div class="form-group required">
                <label class="control-label" for="nominal_name">
                    Имя сертификата
                </label>

                <input type="text"
                       class="form-control"
                       id="nominal_name"
                       v-model="name"
                       min="100"
                       placeholder="Опционально">
                <div class="mini_text">Отображается в публичной части, если не указать,
                    то имя создается автоматически по формату CERT-{НОМИНАЛ},
                    где {НОМИНАЛ} - цена сертификата в рублях</div>
            </div>

            <div class="form-group required">
                <label class="control-label" for="nominal_amount">Кол-во ПС данного номинала (остаток) *</label>

                <input type="number"
                       class="form-control"
                       id="nominal_amount"
                       v-model="qty"
                       required
                       min="0"
                       placeholder="Введите количество">
                <div class="mini_text">Это кол-во уменьшается при каждой покупке сертификата. При достижении 0 - номинал становится не активным.</div>
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

                <div class="mini_text">
                    Кол-во дней отведенных на активацию сертификата. При выставлении 0 - номинал становится не активным.
                </div>
            </div>

            <div class="form-group required">
                <label class="control-label">Доступные дизайны*</label>

                <ul class="designs_list">

                    <li v-for="design in all_designs" :key="'nd-' + design.id" :class="'design_active_' + design.is_active">
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
                <div class="mini_text">
                    Хотя бы один дизайн должен быть отмечен, что бы номинал отображался в публичной части.
                </div>
            </div>
            <div class="form-group required">
                <label class="control-label" for="nominal_status">Активность</label><br>

                <div>
                    <input type="checkbox"
                           :checked="!!is_active"
                           @change="is_active=!is_active"
                           id="nominal_status">
                </div>
                <div class="mini_text">
                    Активность автоматически уберется если остаток = 0, либо срок активации = 0, либо не указаны доступные дизайны
                </div>
            </div>

            <button type="submit" class="btn btn-success">Сохранить</button>
            <button type="button" class="btn" @click="cancel">Отменить</button>

        </form>
    </div>
</template>

<script>
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
            name: '',
            is_active: '',
            activation_period: '',
            qty: '',
            designs: []
        };
    },
    mounted() {
        if (this.nominal) {
            const nominal = this.nominal || {}

            this.price = nominal.hasOwnProperty('price') ? nominal.price : '';
            this.name = nominal.name || '';
            this.is_active = nominal.is_active || 0;
            this.activation_period = nominal.hasOwnProperty('activation_period') ? nominal.activation_period : '';
            this.qty = nominal.hasOwnProperty('qty') ? nominal.qty : '';
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
                name: this.name,
                is_active: this.is_active ? 1 : 0,
                activation_period: this.activation_period,
                qty: this.qty,
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
.nominal-edit-form .design_active_0 {
     color: #999;
 }
.nominal-edit-form .design_active_1 {
    /*color: red;*/
}

</style>
