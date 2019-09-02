<template>
    <div @click="onClose" class="modal-mask">
        <div
                ref="wrapper"
                class="modal-wrapper"
                tabindex="0"
                @click.stop
                @keydown="keyDown"
                :class="{ 'modal-wrapper--fullscreen': type === 'fullscreen' }"
        >
            <div
                    class="modal-container popup"
                    :class="{ [`popup--${type}`]: type, [`popup--${name}`]: name }"
                    @mousedown="onPopupMouseDown"
                    @mouseup="onPopupMouseUp"
            >
                <div class="modal-header popup__header">
                    <slot name="header">
                        <!-- Default header -->
                    </slot>
                </div>

                <div class="modal-body popup__body">
                    <slot name="body">
                        <!-- Default body -->
                    </slot>
                </div>

                <button type="button" title="Закрыть" class="modal-default-button popup__close" @click="close">
                    <svg class="icon icon--cross-big">
                        <use xlink:href="#icon-cross"></use>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
    import './modal.css';

    export default {
        name: 'modal',
        props: {
            type: {
                type: String,
            },
            name: {
                type: String,
            },
            closeOnBtn: {
                type: Boolean,
                default: true,
            },
            isScrollLocked: {
                type: Boolean,
                default: true,
            },
            close: {
                type: Function,
                default: () => {},
            },
        },
        data() {
            return {
                clickInside: false,
                lock: false,
            };
        },
        methods: {
            onPopupMouseDown(e) {
                this.clickInside = true;
            },
            onPopupMouseUp(e) {
                this.clickInside = false;
            },
            onClose(e) {
                if (!this.clickInside) this.close();
                else this.clickInside = false;
            },
            keyDown(e) {
                switch (e.key) {
                    case 'Escape':
                        if (this.closeOnBtn) this.close();
                        e.preventDefault();
                        break;
                }
            },
        },
        mounted() {
            this.lock = this.isScrollLocked;
            this.$refs.wrapper.focus();
        },
        beforeDestroy() {
            this.lock = false;
        },
    };
</script>
