import assert from 'node:assert/strict';
import { normalizeServices, getHealthPercent } from '../resources/js/components/dockerWidgetLogic.js';

const demoServices = [
  { name: 'demo_app', status: 'running', latency: '0ms', load: 86, color: 'mint' },
  { name: 'demo_db', status: 'running', latency: '0ms', load: 41, color: 'mint' },
  { name: 'demo_queue', status: 'stopped', latency: '0ms', load: 0, color: 'amber' },
];

assert.deepEqual(normalizeServices([], demoServices), demoServices);
assert.equal(getHealthPercent(demoServices), 67);
assert.equal(getHealthPercent([]), 0);

console.log('docker widget logic tests passed');
