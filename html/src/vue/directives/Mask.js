import masker from 'vue-the-mask/src/masker';
import tokens from 'vue-the-mask/src/tokens';

// https://developer.mozilla.org/en-US/docs/Web/Guide/Events/Creating_and_triggering_events#The_old-fashioned_way
function event(name) {
    const evt = document.createEvent('Event');
    evt.initEvent(name, true, true);
    return evt;
}

export default {
    bind(el, binding) {
        let config = binding.value;
        if (Array.isArray(config) || typeof config === 'string') {
            config = {
                mask: config,
                tokens,
            };
        }

        if (el.tagName.toLocaleUpperCase() !== 'INPUT') {
            const els = el.getElementsByTagName('input');
            if (els.length !== 1) {
                throw new Error(`v-mask directive requires 1 input, found ${els.length}`);
            } else {
                el = els[0];
            }
        }

        el.oninput = evt => {
            // avoid infinite loop
            if (!evt.isTrusted) return;

            /* other properties to try to diferentiate InputEvent of Event (custom)
            InputEvent (native)
              cancelable: false
              isTrusted: true
              composed: true
              isComposing: false
              which: 0
            Event (custom)
              cancelable: true
              isTrusted: false
            */
            // by default, keep cursor at same position as before the mask

            let position = el.selectionEnd;
            // save the character just inserted
            const digit = el.value[position - 1];
            el.value = config ? masker(el.value, config.mask, true, config.tokens) : el.value;
            // if the digit was changed, increment position until find the digit again
            while (position < el.value.length && el.value.charAt(position - 1) !== digit) {
                position += 1;
            }
            if (el === document.activeElement) {
                el.setSelectionRange(position, position);
                setTimeout(() => {
                    el.setSelectionRange(position, position);
                }, 0);
            }
            el.dispatchEvent(event('input'));
        };

        const newDisplay = config ? masker(el.value, config.mask, true, config.tokens) : el.value;
        if (newDisplay !== el.value) {
            el.value = newDisplay;
            el.dispatchEvent(event('input'));
        }
    },

    update(el, binding) {
        if (binding.value === binding.oldValue) return;

        let config = binding.value;
        if (Array.isArray(config) || typeof config === 'string') {
            config = {
                mask: config,
                tokens,
            };
        }

        if (el.tagName.toLocaleUpperCase() !== 'INPUT') {
            const els = el.getElementsByTagName('input');
            if (els.length !== 1) {
                throw new Error(`v-mask directive requires 1 input, found ${els.length}`);
            } else {
                el = els[0];
            }
        }

        el.oninput = evt => {
            // avoid infinite loop
            if (!evt.isTrusted) return;

            /* other properties to try to diferentiate InputEvent of Event (custom)
            InputEvent (native)
              cancelable: false
              isTrusted: true
              composed: true
              isComposing: false
              which: 0
            Event (custom)
              cancelable: true
              isTrusted: false
            */
            // by default, keep cursor at same position as before the mask

            let position = el.selectionEnd;
            // save the character just inserted
            const digit = el.value[position - 1];
            el.value = config ? masker(el.value, config.mask, true, config.tokens) : el.value;
            // if the digit was changed, increment position until find the digit again
            while (position < el.value.length && el.value.charAt(position - 1) !== digit) {
                position += 1;
            }
            if (el === document.activeElement) {
                el.setSelectionRange(position, position);
                setTimeout(() => {
                    el.setSelectionRange(position, position);
                }, 0);
            }
            el.dispatchEvent(event('input'));
        };

        const newDisplay = config ? masker(el.value, config.mask, true, config.tokens) : el.value;
        if (newDisplay !== el.value) {
            el.value = newDisplay;
            el.dispatchEvent(event('input'));
        }
    },
};
