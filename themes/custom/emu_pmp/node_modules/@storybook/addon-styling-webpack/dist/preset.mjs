import { logger, colors } from '@storybook/node-logger';

var r=t=>t.test instanceof RegExp&&(t.test.test("test.css")||t.test.test("test.scss")||t.test.test("test.less"));function p(t,{rules:o,plugins:e}={}){if(logger.info(`=> [${colors.pink("@storybook/addon-styling-webpack")}] Applying custom Storybook webpack configuration styling.`),e&&e.length&&(logger.info(`=> [${colors.pink("@storybook/addon-styling-webpack")}] Adding given plugins to Storybook webpack configuration.`),t.plugins=t.plugins||[],t.plugins.push(...e)),o&&o.length){if(logger.info(`=> [${colors.pink("@storybook/addon-styling-webpack")}] Replacing Storybook's webpack rules for styles with given rules.`),!t.module?.rules)throw new Error("webpackFinal received a rules option but config.module.rules is not an array");t.module.rules=t.module.rules.filter(i=>typeof i=="object"&&!r(i)),t.module.rules?.push(...o);}return t}var u=p;

export { u as webpackFinal };
//# sourceMappingURL=out.js.map
//# sourceMappingURL=preset.mjs.map