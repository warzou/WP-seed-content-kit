'use strict';
const fs = require('fs');
const vm = require('vm');
const assert = require('assert');
const filters = {}, actions = {};
let component, saved;
const window = {
    vendor: {wp: {hooks: {addFilter: (name, ns, fn) => { filters[name] = fn; }, addAction: (name, ns, fn) => { actions[name] = fn; }}},
        React: {createElement: (type, props, ...children) => ({type, props, children})}},
    divi: {fieldLibrary: {Select: function Select() {}, registerFieldComponent: value => { component = value.component; }}},
    WpsckCalendarFiltersData: {program: [{id:'1',label:'Programme A'}], year:{2026:'2026',2027:'2027'},
        section:[{id:'3',program:'1',label:'Premiere annee'},{id:'9',program:'2',label:'Autre programme'}], theme:[], status:{planned:'Prevu'}}
};
vm.runInNewContext(fs.readFileSync(__dirname + '/../plugin/includes/integrations/divi/formation-calendar-filters.js','utf8'),{window});
actions['divi.moduleLibrary.registerModuleLibraryStore.after']();
assert(component);
const attrs = {
    module:{advanced:{loop:{desktop:{value:{subTypes:[{value:'seed_schedule'}]}}}}},
    wpsckCalendarFilters:{innerContent:{desktop:{value:{enabled:'on',program:'1',year:'2026'}}}}
};
const query = filters['divi.module.layout.childModule.loop.resultsQueryParams'];
let params = query(new URLSearchParams(),attrs,'module-1','post_types');
assert.equal(JSON.parse(params.get('wpsck_calendar_filters')).year,'2026');
assert.equal(query(new URLSearchParams(),attrs,'m','terms').toString(),'');
attrs.module.advanced.loop.desktop.value.subTypes.push({value:'seed_directory'});
assert.equal(query(new URLSearchParams(),attrs,'m','post_types').toString(),'');
attrs.module.advanced.loop.desktop.value.subTypes.pop();
attrs.wpsckCalendarFilters.innerContent.desktop.value.enabled='off';
assert.equal(query(new URLSearchParams(),attrs,'m','post_types').toString(),'');
const output=component({value:{enabled:'on',program:'1'},onChange:v=>{saved=v;}});
const controls=output.children[0];
assert.equal(controls.length,7);
const section=controls[3].children[1];
assert(section.props.options['3']);
assert(!section.props.options['9']);
const program=controls[1].children[1];
program.props.onChange({inputValue:'2'});
assert.equal(saved.program,'2');
assert.equal(saved.section,'');
assert.equal(section.props.options['0'].label,'Toutes les sections');
section.props.onChange({inputValue:'0'});
assert.equal(saved.section,'');
assert.notEqual(section.props.name,program.props.name);
const register=filters['divi.moduleLibrary.moduleAttributes'];
assert(register({}, {name:'divi/group'}).wpsckCalendarFilters);
assert(!register({}, {name:'divi/text'}).wpsckCalendarFilters);
console.log('Formation calendar filters: 15 assertions PASS');
