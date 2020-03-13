<template>
    <draggable tag="ul" :list="dragList" v-bind="dragOptions" group="widgets" @change="handleChange">
        <li class="widget-preview"
            :class="{'is-active': selectedWidget && widget.id === selectedWidget.id}"
            v-for="widget in dragList"
            :key="widget.id"
        >
            <button type="button" class="widget-preview__remove" title="Удалить" @click="removeWidget(widget)">
                <span aria-hidden="true">×</span>
            </button>
            <div class="widget-preview__picture" @click="selectWidget(widget)">
                <img :src="widget.previewSmall" :alt="widget.name">
            </div>
            <p class="widget-preview__name" @click="selectWidget(widget)">{{ widget.name }}</p>
        </li>
    </draggable>
</template>

<script>
    import draggable from 'vuedraggable';

    export default {
        props: ['widgets', 'selectedWidget'],
        data() {
            return {
                dragList: [],
            };
        },
        methods: {
            handleChange(event) {
                if (event.moved) {
                    const { moved } = event;
                    const movedWidget = this.widgets[moved.newIndex];
                    const newIndex = moved.newIndex >= moved.oldIndex
                        ? this.widgets[moved.newIndex - 1].contentOrder
                        : this.widgets[moved.newIndex + 1].contentOrder;
                    this.$emit('swapContentItems', {
                        element: moved.element,
                        oldIndex: movedWidget.contentOrder,
                        newIndex: newIndex,
                    });
                } else if (event.added) {
                    const { added } = event;
                    const addedWidget = added.element;
                    const newIndex = added.newIndex > 0 ? this.widgets[added.newIndex - 1].contentOrder + 1 : 0;
                    this.$emit('addContentItem', {
                        element: addedWidget,
                        newIndex: newIndex,
                    });
                }
            },
            selectWidget(widget) {
                this.$emit('update:selectedWidget', widget);
            },
            removeWidget(widgetToRemove) {
                this.dragList = this.dragList.filter((widget) => widget.id !== widgetToRemove.id);
                this.$emit('removeUsedWidget', widgetToRemove);
            }
        },
        computed: {
            dragOptions() {
                return {
                    animation: 200,
                    group: "description",
                    disabled: false,
                    ghostClass: "ghost"
                };
            }
        },
        watch: {
            widgets(newValue, oldValue) {
                if (newValue === oldValue || newValue === this.dragList) return;

                this.dragList = newValue;
            }
        },
        mounted() {
            this.dragList = this.widgets;
        },
        components: {
            draggable,
        }
    }
</script>
