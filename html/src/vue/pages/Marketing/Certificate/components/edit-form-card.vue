<template>
    <div class="card-edit-form">
        <form @submit.prevent="onSubmit">
            <div class="form-group required">
                <label class="control-label" for="card_comment">
                    Поздравление получателю
                </label>

                <textarea type="text"
                        class="form-control"
                        id="card_comment"
                        v-model="comment"
                        placeholder="Введите поздравление получателю ПС" />
                <div class="mini_text">Поздравление получателю которое было введено в карточке ПС на веб-витрине</div>
            </div>

            <div class="form-group required">
                <label class="control-label" for="card_to_name">
                    Имя "Кому"
                </label>

                <input type="text"
                        class="form-control"
                        id="card_to_name"
                        v-model="to_name"
                        placeholder="Введите имя &quot;Кому&quot;">
                <div class="mini_text">Имя "Кому" из карточки ПС на веб-витрине</div>
            </div>

            <div class="form-group required">
                <label class="control-label" for="card_to_email">
                    Email "Кому"
                </label>

                <input type="email"
                        class="form-control"
                        id="card_to_email"
                        v-model="to_email"
                        placeholder="Введите Email &quot;Кому&quot;">
                <div class="mini_text">Email "Кому" из карточки ПС на веб-витрине</div>
            </div>

            <div class="form-group required">
                <label class="control-label" for="card_to_phone">
                    Телефон "Кому"
                </label>

                <input type="phone"
                        class="form-control"
                        id="card_to_phone"
                        v-model="to_phone"
                        placeholder="Введите телефон &quot;Кому&quot;">
                <div class="mini_text">Телефон "Кому" из карточки ПС на веб-витрине</div>
            </div>

            <div class="form-group required">
                <label class="control-label" for="card_from_name">
                    Имя "От кого"
                </label>

                <input type="text"
                        class="form-control"
                        id="card_from_name"
                        v-model="from_name"
                        placeholder="Введите имя &quot;От кого&quot;">
                <div class="mini_text">Имя "От кого" из карточки ПС на веб-витрине</div>
            </div>

            <div class="form-group required">
                <label class="control-label" for="card_from_email">
                    Email "От кого"
                </label>

                <input type="email"
                        class="form-control"
                        id="card_from_email"
                        v-model="from_email"
                        placeholder="Введите Email &quot;От кого&quot;">
                <div class="mini_text">Email "От кого" из карточки ПС на веб-витрине</div>
            </div>

            <div class="form-group required">
                <label class="control-label" for="card_from_phone">
                    Номер телефона "От кого"
                </label>

                <input type="phone"
                        class="form-control"
                        id="card_from_phone"
                        v-model="from_phone"
                        placeholder="Введите телефон &quot;От кого&quot;">
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
            to_name: null,
            to_email: null,
            to_phone: null,
            from_name: null,
            from_email: null,
            from_phone: null,
        }
    },
    mounted() {
        if (this.card) {
            const card = this.card || {}

            this.comment = card.comment || ''
            this.to_name = card.to_name || ''
            this.to_email = card.to_email || ''
            this.to_phone = card.to_phone || ''
            this.from_name = card.from_name || ''
            this.from_email = card.from_email || ''
            this.from_phone = card.from_phone || ''
        }
    },
    methods: {
        onSubmit() {
            this.$emit('save', {
                comment: this.comment,
                to_name: this.to_name,
                to_email: this.to_email,
                to_phone: this.to_phone,
                from_name: this.from_name,
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