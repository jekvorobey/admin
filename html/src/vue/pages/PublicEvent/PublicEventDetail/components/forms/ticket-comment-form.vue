<template>
    <div>
        <div class="form-group">
            <label for="description">Комментарий</label>
            <ckeditor class="custom-width" id="comment" type="classic" v-model="$v.form.comment.$model" />
        </div>
        <div class="form-group mt-3">
            <button @click="save" class="btn btn-dark" :disabled="!$v.$anyDirty">Сохранить</button>
        </div>
    </div>
</template>

<script>
import {validationMixin} from 'vuelidate';

import {mapActions} from "vuex";
import VueCkeditor from '../../../../../plugins/VueCkeditor';

export default {
    mixins: [validationMixin],
    components: {
        VueCkeditor
    },
    props: {
        ticket: {}
    },
    data() {
        return {
            form: {
                comment: this.ticket.comment,
            },
        };
    },
    validations: {
        form: {
            comment: {},
        }
    },
    methods: {
        async save() {
            Services.net().post(this.getRoute('public-event.tickets.editComment', {event_id: this.publicEvent.id}), {}, {
                ticketId: this.ticket.id,
                comment: this.form.comment,
            }).then(() => {
                this.$emit('onSave');
            });
        }
    },
}
</script>

<style lang="css">

</style>