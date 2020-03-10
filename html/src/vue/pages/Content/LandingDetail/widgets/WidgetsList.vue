<template>
    <div class="widgets-list">
        <draggable tag="ul"
                   :list="dragList"
                   v-bind="dragOptions"
                   :sort="false"
                   :group="{ name: 'widgets', pull: 'clone', put: false }"
                   @start="hideTooltips"
                   @end="reloadTooltips"
                   @change="handleChange"
                   :clone="cloneWidget">
            <li v-for="widget in dragList" :key="widget.id">
                <button type="button"
                        class="btn btn-primary widgets-list__item"
                        data-toggle="tooltip"
                        :title="'<img src=\'' + widget.previewBig + '\' class=\'widgets-list__item__src\' />'"
                >
                    {{ widget.name }}
                </button>
            </li>
        </draggable>

        <div id="widgets-list-tooltip"></div>
    </div>
</template>

<script>
    import draggable from 'vuedraggable';

    export default {
        props: ['widgetsList', 'cloneWidget'],
        data() {
            return {
                dragList: [],
            };
        },
        methods: {
            reloadTooltips() {
                /*
                TODO
                $('.widgets-list button[data-toggle="tooltip"]').tooltip('dispose').tooltip({
                    placement: 'right',
                    html: true,
                    boundary: 'window',
                    container: '#widgets-list-tooltip',
                });
                $('#widgets-list-tooltip').show();
                */
            },
            hideTooltips() {
                /*
                TODO
                $('#widgets-list-tooltip').hide();
                */
            },
            handleChange(event) {
                const { moved } = event;
                const oldDragOrder = moved.element.dragWidgetsListOrder;
                const newDragOrder = oldDragOrder + moved.newIndex - moved.oldIndex;
                this.dragList = this.dragList.map((widget) => {
                    if (widget.dragWidgetsListOrder === oldDragOrder) {
                        return { ...widget, dragWidgetsListOrder: newDragOrder };
                    }

                    if (oldDragOrder <= newDragOrder && widget.dragWidgetsListOrder > oldDragOrder && widget.dragWidgetsListOrder <= newDragOrder) {
                        return { ...widget, dragWidgetsListOrder: widget.dragWidgetsListOrder - 1 };
                    }

                    if (oldDragOrder > newDragOrder && widget.dragWidgetsListOrder >= newDragOrder && widget.dragWidgetsListOrder < oldDragOrder) {
                        return { ...widget, dragWidgetsListOrder: widget.dragWidgetsListOrder + 1 };
                    }

                    return widget;
                });

                this.$emit('swapWidgetsListItems', moved);
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
            widgetsList(newValue, oldValue) {
                if (newValue === oldValue || newValue === this.dragList) return;

                this.dragList = newValue;
            }
        },
        updated() {
            this.reloadTooltips();
        },
        mounted() {
            this.dragList = this.widgetsList;
            this.reloadTooltips();
        },
        components: {
            draggable,
        }
    }
</script>
