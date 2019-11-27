<template>
    <div>
        <v-select v-model="$v.form.package_id.$model" :options="packageOptions">
            Тип коробки
        </v-select>
        <button @click="save" class="btn btn-dark mt-3" :disabled="!$v.form.$anyDirty">Сохранить</button>
    </div>
</template>

<script>
    import {validationMixin} from 'vuelidate';
    import {required} from 'vuelidate/lib/validators';

    import Services from "../../../../../../../scripts/services/services";
    import {mapGetters} from "vuex";

    import VSelect from "../../../../../../components/controls/VSelect/VSelect.vue";

    export default {
        components: {
            VSelect,
        },
        mixins: [validationMixin],
        props: {
            shipment: {},
            packages: {},
        },
        data () {
            return {
                form: {
                    package_id: null
                }
            };
        },
        validations: {
            form: {
                package_id: {required},
            }
        },
        methods: {
            save() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }
                Services.net().post(this.getRoute('shipment.addShipmentPackage', {id: this.shipment.id}), {}, {
                    package: this.form,
                })
                    .then(result => {
                        this.$emit('onSave', result);
                    });
            },
        },
        computed: {
            ...mapGetters(['getRoute']),
            packageOptions() {
                return Object.values(this.packages).map(item => ({value: item.id, text: item.name}));
            },
        }
    }
</script>
