const path = require("path");
const webpack_configurator = require("../../tools/utils/scripts/webpack-configurator.js");

const context = __dirname;
const output = webpack_configurator.configureOutput(path.resolve(__dirname, "./frontend-assets/"));

module.exports = [
    {
        entry: {
            "onlyoffice-documents": "./scripts/onlyoffice/src/index.ts",
            "onlyoffice-editor": "./scripts/onlyoffice/src/editor.js"
        },
        context,
        output,
        externals: {
            tlp: "tlp"
        },
        module: {
            rules: [
                ...webpack_configurator.configureTypescriptRules(),
                webpack_configurator.rule_easygettext_loader,
                webpack_configurator.rule_vue_loader
            ]
        },
        plugins: [
            webpack_configurator.getManifestPlugin(),
            webpack_configurator.getVueLoaderPlugin(),
            webpack_configurator.getTypescriptCheckerPlugin(true)
        ],
        resolveLoader: {
            alias: webpack_configurator.easygettext_loader_alias
        }
    }
];