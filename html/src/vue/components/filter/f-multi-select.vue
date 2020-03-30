<template>
    <div class="form-group">
        <label :for="id">
            <slot/>
            <fa-icon v-if="$slots.help" icon="question-circle" v-b-popover.hover="$slots.help[0].text"></fa-icon>
        </label>
        <div class="input-group input-group-sm">
            <div v-if="$slots.prepend" class="input-group-prepend">
                <slot name="prepend"/>
            </div>
            <div class="form-control" @click="toggleList">
                <span v-for="item in items" @click="e => {e.stopPropagation(); remove(item.value)}"
                      class="badge badge-secondary mr-2">
                    {{item.text}} <fa-icon icon="times"></fa-icon>
                </span>
                <fa-icon icon="caret-down" class="float-right"></fa-icon>
            </div>
            <div v-if="$slots.append" class="input-group-append">
                <slot name="append"/>
            </div>
        </div>
        <transition name="slide">
            <div v-if="opened" class="select-list" @mouseleave="toggleList">
                <template v-if="grouped">
                    <template v-for="group in groups">
                        <div class="select-item select-item--heading">{{ group }}</div>
                        <div
                                v-for="option in groupOptions(group)"
                                class="select-item"
                                :class="{selected:valueSelected(option.value)}"
                                @click="toggleOption(option.value)"
                        >{{ option.text }}</div>
                    </template>
                </template>
                <template v-else>
                    <div
                            v-for="option in options"
                            class="select-item"
                            :class="{selected:valueSelected(option.value)}"
                            @click="toggleOption(option.value)"
                    >{{ option.text }}</div>
                </template>
            </div>
        </transition>
    </div>
</template>

<script>
    export default {
        name: 'f-multi-select',
        inheritAttrs: false,
        props: {
            value: {},
            options: Array,
            grouped: {
                type: Boolean,
                default: false
            }
        },
        data() {
            return {
                opened: false,
            };
        },
        computed: {
            id() {
                return `filter-select-${this._uid}`
            },
            inputListeners() {
                return Object.assign({}, this.$listeners, {input: this.input});
            },
            items() {
                return this.options.filter(option => this.valueSelected(option.value));
            },
            groups() {
                return [...this.options.reduce((res, value) => {
                    res.add(value.group);
                    return res;
                }, new Set())];
            }
        },
        methods: {
            input(e) {
                this.$emit('input', e.target.value);
            },
            toggleList() {
                this.opened = !this.opened;
            },
            remove(value) {
                this.value.splice(this.value.indexOf(value), 1);
                this.$emit('input', this.value);
            },
            toggleOption(value) {
                if (this.valueSelected(value)) {
                    this.remove(value);
                } else {
                    this.value.push(value);
                    this.$emit('input', this.value);
                }
            },
            valueSelected(value) {
                return this.value.indexOf(value) !== -1;
            },
            groupOptions(group) {
                return this.options.filter(option => option.group === group);
            }
        },
    }
</script>

<style scoped>
    .select-list {
        position: absolute;
        background: white;
        border: 1px solid #DFDFDF;
        width: calc( 100% - 30px );
        z-index: 9999;
        max-height: 300px;
        overflow-y: auto;
    }
    .select-item {
        padding: 5px 10px;
        user-select: none;
    }
    .select-item--heading {
        font-weight: bold;
        text-transform: uppercase;
    }
    .select-item:hover:not(.select-item--heading), .selected {
        background: #F7F7F7;
    }
    .select-item:not(:last-of-type) {
        border-bottom: 1px solid #DFDFDF;
    }
</style>
