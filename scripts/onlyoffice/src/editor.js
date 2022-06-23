(function ($, tlp) {
    $(document).ready(() => {
        var frameEditor = document.getElementById("iframeEditor");
        var fileId = frameEditor.getAttribute("data-id");

        var configUrl = location.origin + "/api/onlyoffice/editor/config/" + fileId;

        $.ajax({
            url: configUrl,
            success: function onSuccess(config) {
                if (typeof DocsAPI === "undefined") {
                    return;
                }

                var docEditor = new DocsAPI.DocEditor("iframeEditor", config);
            }
        });
    });
})(jQuery, tlp);