import ts from 'vue-the-mask/src/tokens';
import mr from 'vue-the-mask/src/masker';
import mask from '../directives/Mask';

const MaskPlugin = vue => {
    vue.directive('mask', mask);
};

export default MaskPlugin;
export const tokens = ts;
export const masker = mr;
