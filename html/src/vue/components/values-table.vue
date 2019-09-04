<template>
    <table class="values-table">
        <tbody>
            <tr v-for="(name, key) in names">
                <th>{{ name }}</th>
                <td>{{ values[key] }}</td>
            </tr>
        </tbody>
    </table>
</template>

<script>
    export default {
        props: {
            values: {},
            names: {}
        }
    }
</script>

<style>
    .values-table th {
        text-align: end;
        padding-right: 8px;
    }
    .values-table td {
        padding-left: 8px;
    }
</style>