export default {
    methods: {
        showCase(code) {
            window.open(process.env.SHOWCASE_HOST + '/product/' + code, '_blank');
        },
    },
};