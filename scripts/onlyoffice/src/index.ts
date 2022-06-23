import Vue from "vue";

import OpenFile from "./components/OpenFile.vue";

document.addEventListener("DOMContentLoaded", async () => {
    const OpenFileComponent = Vue.extend(OpenFile);

    var fileItems = document.getElementsByClassName("document-tree-item-file");

    //file action build for test
    setTimeout(function () {
        for (var i = 0; i < fileItems.length; i++) {
            var item = fileItems[i];
            var fileId = item.getAttribute("data-item-id");
            var container = document.getElementById("document-folder-content-row-div-" + fileId);

            var mountPoint = document.createElement("div");
            container?.appendChild(mountPoint);

            new OpenFileComponent({
                propsData: {
                    fileId: Number(fileId)
                }
            }).$mount(mountPoint);
        }
    }, 1000);
});