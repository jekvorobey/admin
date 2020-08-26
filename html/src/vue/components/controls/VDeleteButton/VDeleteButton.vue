<template>
    <span style="display: inline-block; vertical-align: middle;">
        <button :id="btn_id()" type="button" @click="onClick" class="btn" :class="btnClass" title="Удалить">
            <fa-icon icon="trash-alt"/>
        </button>
        <b-popover :show.sync="popoverShow" placement="auto" ref="popover" :target="btn_id()">
            <template slot="title">
                <b-button @click="onClose" class="close ml-3" aria-label="Close">
                    <span class="d-inline-block" aria-hidden="true">&times;</span>
                </b-button>
                Подтвердите удаление
            </template>
            <div>
                <button @click="onClose" class="btn btn-sm btn-secondary">Отмена</button>
                <button @click="onOk" class="btn btn-sm btn-danger">Удалить</button>
            </div>
        </b-popover>
    </span>
</template>

<script>
let input_count = 0;
function newBtnId() {
    return `btn-delete-${++input_count}`;
}

export default {
    name: 'v-delete-button',
    props: {
        btnClass: {
            type: String,
            default: 'btn-danger',
        },
    },
    data() {
        return {
            popoverShow: false,
            input_id: undefined
        };
    },
    methods: {
        btn_id() {
            if (this.input_id === undefined) {
                this.input_id = newBtnId();
            }
            return this.input_id;
        },
        onClick() {
            this.popoverShow = !this.popoverShow;
        },
        onClose() {
            this.popoverShow = false
        },
        onOk() {
            this.$emit('delete');
            this.onClose();
        },
    }
}
</script>