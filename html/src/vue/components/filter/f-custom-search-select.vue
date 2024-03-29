<template>
    <div class="form-group">
        <label :for="id">
            <slot />
            <fa-icon v-if="$slots.help" icon="question-circle" v-b-popover.hover="$slots.help[0].text"></fa-icon>
        </label>
        <div class="input-group input-group-sm">
            <div v-if="$slots.prepend" class="input-group-prepend"><slot name="prepend"/></div>
            <div class="aselect" :id="id">
                <div class="selector" @click="toggle($event.target)" :class="{'isDisabled': isDisabled}">
                    <div class="label">
                        <span>{{ myValue[0].text}}</span>
                    </div>
                    <div class="arrow" :class="{ expanded : visible }"></div>
                    <div :class="{ hidden : !visible, visible }">
                        <v-search-input @onSearch="onSearch" :value="inputSearch" class="li-search"/>
                        <ul class="inner-list">
                            <li v-if="!without_none" @click="select('')">Не выбрано</li>
                            <li :class="{ current : option === myValue }" v-for="option in filteredOptions" @click="select(option)">{{ option.text }}</li>
                        </ul>
                    </div>
                </div>
            </div>
            <span class="custom-search-error" v-if="error">{{ error }}</span>
            <div v-if="$slots.append" class="input-group-append"><slot name="append"/></div>
        </div>
        <div v-if="help" class="form-text text-muted small"> {{ help }} </div>
    </div>
</template>

<script>
    import VSearchInput from "../controls/VSearchInput/VSearchInput.vue";
    export default {
        name: 'f-custom-search-select',
        inheritAttrs: false,
        emits: ['onSelect'],
        components: {VSearchInput},
        data(){
            return{
                visible: false,
                inputSearch: ''
            }
        },
        props: {
            value: [Number, String],
            options: Array,
            without_none: Boolean,
            isDisabled: {
                type: Boolean,
                default: false,
                required: false
            },
            error: [String, Object],
            help: {
                type: [String, Object],
                default: false,
                required: false
            }
        },
        computed: {
            myValue(){
                return this.options.length > 1 && this.value !== '' && this.value != null ? this.options.filter(option => option.value == this.value) : [{text:'Не выбрано'}];
            },
            id() {
                return `filter-select-${this._uid}`
            },
            filteredOptions(){
                if (this.inputSearch !== null && this.inputSearch !== ''){
                    return this.options.filter(option => {
                            if (option.text !== undefined){
                                return option.text.toLowerCase().includes(this.inputSearch.toLowerCase())
                            }
                        }
                    )
                }
                else {
                    return this.options
                }
            },
        },
        methods: {
            toggle(target) {
                if(!this.isDisabled){
                    if(!target.classList.contains('search-input') && !target.classList.contains('search-input-close-btn')){
                        this.visible = !this.visible;
                    }
                }
            },
            select(option) {
                option === '' ? this.$emit('input', null) : this.$emit('input', option.value);
            },
            onSearch(value){
                this.inputSearch = value
            }
        },
    }
</script>

<style>
    .li-search{
        z-index: 1;
        background: #fff;
    }
    .li-search .input-close-btn {
        top: 33px;
    }
    .inner-list{
        max-height: 300px;
        overflow-y: auto;
    }
    .aselect {
        width: 100%;
        cursor: pointer;
    }
    .aselect .selector {
        border: 1px solid gainsboro;
        border-radius: 4px;
        background: #ffffff;
        position: relative;
    }
    .aselect .selector .arrow {
        position: absolute;
        right: 10px;
        top: 27%;
        width: 0;
        height: 0;
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-top: 5px solid #495057;
        transform: rotateZ(0deg) translateY(0px);
        transition-duration: 0.3s;
        transition-timing-function: cubic-bezier(0.59, 1.39, 0.37, 1.01);
    }
    .aselect .selector .expanded {
        transform: rotateZ(180deg) translateY(2px);
    }
    .aselect .selector .label {
        display: block;
        padding: 2px 6px;
        font-size: 16px;
        color: #888;
    }
    .aselect ul {
        width: 100%;
        max-height: 380px;
        overflow-y: auto;
        list-style-type: none;
        padding: 0;
        margin: 0;
        font-size: 16px;
        border: 1px solid gainsboro;
        position: absolute;
        z-index: 10;
        background: #fff;
    }
    .aselect li {
        padding: 6px;
        color: #666;
        border-bottom: 1px solid #8080804d;
    }
    .aselect li:hover {
        color: white;
        background: rgba(196, 215, 205, 0.83);
    }
    .aselect .current {
        background: #eaeaea;
    }
    .aselect .hidden {
        visibility: hidden;
        max-height: 0;
    }
    .aselect .visible {
        visibility: visible;
        max-height: 500px;
    }
    .isDisabled{
        background: #e9ecef !important;
    }
    .custom-search-error{
        width: 100%;
        margin-top: 0.25rem;
        font-size: 80%;
        color: #dc3545;
    }
</style>