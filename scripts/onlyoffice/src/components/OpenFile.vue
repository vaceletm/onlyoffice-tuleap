<template>
    <button v-on:click="open">Open in ONLYOFFICE</button>
</template>

<script>

export default {
    name: "OpenFile",
    props: {
        fileId: Number
    },
    methods: {
        open: function (event) {
            window.open(location.origin + "/plugins/onlyoffice/editor?fileId=" + this.fileId, "_blank");
        }
    }
}
</script>