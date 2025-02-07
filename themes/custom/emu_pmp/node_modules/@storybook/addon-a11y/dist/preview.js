'use strict';

var matchers = require('vitest-axe/matchers');
var test = require('@storybook/test');
var previewApi = require('storybook/internal/preview-api');
var global = require('@storybook/global');

function _interopNamespace(e) {
	if (e && e.__esModule) return e;
	var n = Object.create(null);
	if (e) {
		Object.keys(e).forEach(function (k) {
			if (k !== 'default') {
				var d = Object.getOwnPropertyDescriptor(e, k);
				Object.defineProperty(n, k, d.get ? d : {
					enumerable: true,
					get: function () { return e[k]; }
				});
			}
		});
	}
	n.default = e;
	return Object.freeze(n);
}

var matchers__namespace = /*#__PURE__*/_interopNamespace(matchers);

var ADDON_ID="storybook/a11y";var RESULT=`${ADDON_ID}/result`,REQUEST=`${ADDON_ID}/request`,RUNNING=`${ADDON_ID}/running`,ERROR=`${ADDON_ID}/error`,MANUAL=`${ADDON_ID}/manual`;var EVENTS={RESULT,REQUEST,RUNNING,ERROR,MANUAL},A11Y_TEST_TAG="a11y-test";var{document: document$1}=global.global,channel=previewApi.addons.getChannel(),defaultParameters={config:{},options:{}},disabledRules=["region"],queue=[],isRunning=!1,runNext=async()=>{if(queue.length===0){isRunning=!1;return}isRunning=!0;let next=queue.shift();next&&await next(),runNext();},run=async(input=defaultParameters)=>{let{default:axe}=await import('axe-core'),{element="body",config={},options={}}=input,htmlElement=document$1.querySelector(element)??document$1.body;if(!htmlElement)return;axe.reset();let configWithDefault={...config,rules:[...disabledRules.map(id=>({id,enabled:!1})),...config?.rules??[]]};return axe.configure(configWithDefault),new Promise((resolve,reject)=>{let task=async()=>{try{let result=await axe.run(htmlElement,options);resolve(result);}catch(error){reject(error);}};queue.push(task),isRunning||runNext();})};channel.on(EVENTS.MANUAL,async(storyId,input=defaultParameters)=>{try{let result=await run(input),resultJson=JSON.parse(JSON.stringify(result));channel.emit(EVENTS.RESULT,resultJson,storyId);}catch(error){channel.emit(EVENTS.ERROR,error);}});function getIsVitestStandaloneRun(){try{return undefined.VITEST_STORYBOOK==="false"}catch{return !1}}function getIsVitestRunning(){try{return undefined.MODE==="test"}catch{return !1}}test.expect.extend(matchers__namespace);var experimental_afterEach=async({reporting,parameters,globals,tags})=>{let a11yParameter=parameters.a11y,a11yGlobals=globals.a11y;if(a11yParameter?.manual!==!0&&a11yParameter?.disable!==!0&&a11yGlobals?.manual!==!0){if(getIsVitestRunning()&&!tags.includes(A11Y_TEST_TAG))return;try{let result=await run(a11yParameter);if(result){let hasViolations=(result?.violations.length??0)>0;reporting.addReport({type:"a11y",version:1,result,status:hasViolations?"failed":"passed"}),getIsVitestStandaloneRun()&&hasViolations&&test.expect(result).toHaveNoViolations();}}catch(e){if(reporting.addReport({type:"a11y",version:1,result:{error:e},status:"failed"}),getIsVitestStandaloneRun())throw e}}},initialGlobals={a11y:{manual:!1}};

exports.experimental_afterEach = experimental_afterEach;
exports.initialGlobals = initialGlobals;
