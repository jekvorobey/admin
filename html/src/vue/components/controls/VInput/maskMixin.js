import { tokens, masker } from '../../../plugins/MaskPlugin';

export default {
    props: {
        value: [String, Number],
        mask: {
            type: [String, Array],
            required: true,
        },
        masked: {
            // by default emits the value unformatted, change to true to format with the mask
            type: Boolean,
            default: false, // raw
        },
        tokens: {
            type: Object,
            default: () => tokens,
        },
    },
    data() {
        return {
            lastValue: null, // avoid unecessary emit when has no change
            display: this.value,
        };
    },
    watch: {
        value(newValue) {
            if (newValue !== this.lastValue) {
                this.display = newValue;
            }
        },
        masked() {
            this.refresh(this.display);
        },
    },
    computed: {
        config() {
            return {
                mask: this.mask,
                tokens: this.tokens,
                masked: this.masked,
            };
        },
    },
    methods: {
        input(e) {
            this.refresh(e.target.value);
        },

        refresh(value) {
            this.display = value;
            const maskedValue = masker(value, this.mask, this.masked, this.tokens);
            if (maskedValue !== this.lastValue) {
                this.lastValue = maskedValue;
                this.$emit('input', maskedValue);
            }
        },
    },
};
