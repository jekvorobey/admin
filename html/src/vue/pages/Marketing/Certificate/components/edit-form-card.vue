<template>
    <div class="card-edit-form">
        <form @submit.prevent="onSubmit">
            <div class="form-group required">
                <label class="control-label" for="card_comment">
                    Сообщение из карточки ПС
                </label>

                <textarea type="text"
                        class="form-control"
                        id="card_comment"
                        v-model="comment"
                        placeholder="Введите сообщение получателю ПС" />
                <div class="mini_text">Сообщение которое было введено на веб-витрине в карточке ПС</div>
            </div>

            <div class="form-group required">
                <label class="control-label" for="card_to_email">
                    Email получателя ПС
                </label>

                <input type="email"
                        class="form-control"
                        id="card_to_email"
                        v-model="to_email"
                        placeholder="Введите Email получателя ПС">
                <div class="mini_text">Email получателя из карточки ПС на веб-витрине</div>
            </div>

            <div class="form-group required">
                <label class="control-label" for="card_to_phone">
                    Номер телефона получателя
                </label>

                <input type="phone"
                        class="form-control"
                        id="card_to_phone"
                        v-model="to_phone"
                        placeholder="Введите телефон получателя ПС">
                <div class="mini_text">Телефон получателя из карточки ПС на веб-витрине</div>
            </div>

            <div class="form-group required">
                <label class="control-label" for="card_from_email">
                    Email "От кого" ПС
                </label>

                <input type="email"
                        class="form-control"
                        id="card_from_email"
                        v-model="from_email"
                        placeholder="Введите Email &quot;От кого&quot; ПС">
                <div class="mini_text">Email "От кого" из карточки ПС на веб-витрине</div>
            </div>

            <div class="form-group required">
                <label class="control-label" for="card_from_phone">
                    Номер телефона "От кого" ПС
                </label>

                <input type="phone"
                        class="form-control"
                        id="card_from_phone"
                        v-model="from_phone"
                        placeholder="Введите телефон &quot;От кого&quot; ПС">
                <div class="mini_text">Телефон "От кого" из карточки ПС на веб-витрине</div>
            </div>

            <button type="submit" class="btn btn-success">Сохранить</button>
            <button type="button" class="btn" @click="cancel">Отменить</button>

        </form>
    </div>
</template>

<script>
export default {
    name: 'card-edit-form',
    props: {
        card: {
            type: Object,
            default: () => ({})
        }
    },
    data() {
        return {
            comment: null,
            to_email: null,
            to_phone: null,
            from_email: null,
            from_phone: null,
        }
    },
    mounted() {
      // ...
    },
    methods: {
        onSubmit() {
            this.$emit('save', {
                comment: this.comment,
                to_email: this.to_email,
                to_phone: this.to_phone,
                from_email: this.from_email,
                from_phone: this.from_phone
            })
        },
        cancel() {
            this.$emit('cancel')
        }
    }
}
</script>

<style>
.card-edit-form .mini_text {
    font-size: 14px;
    color: #929292;
}
</style>