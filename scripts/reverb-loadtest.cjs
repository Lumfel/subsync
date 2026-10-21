/* Reverb socket load test.

   Opens N concurrent WebSocket clients against the local Reverb server,
   subscribes them all to the shared "subsync" channel, fires a few test
   broadcasts through Reverb's HTTP API and reports connect times, delivery
   ratio and per-client latency. Read-only: never touches the database.

   Usage (from the repo root, Reverb must be running):
     node scripts/reverb-loadtest.cjs [connections] [events]

   Defaults: 60 connections, 3 events. Example:
     node scripts/reverb-loadtest.cjs 150 5
*/
const Pusher = require('pusher-js');
const crypto = require('crypto');
const http = require('http');
const fs = require('fs');
const path = require('path');

const connections = Math.max(1, parseInt(process.argv[2] || '60', 10));
const eventCount = Math.max(1, parseInt(process.argv[3] || '3', 10));
const eventName = 'loadtest-event';

function readEnv() {
  const envPath = path.join(__dirname, '..', '.env');
  const env = {};
  for (const line of fs.readFileSync(envPath, 'utf8').split(/\r?\n/)) {
    const m = line.match(/^([A-Z0-9_]+)=(.*)$/);
    if (m) env[m[1]] = m[2].trim();
  }
  return env;
}

const env = readEnv();
const appId = env.REVERB_APP_ID;
const appKey = env.REVERB_APP_KEY;
const appSecret = env.REVERB_APP_SECRET;
const host = '127.0.0.1';
const port = parseInt(env.REVERB_PORT || '8080', 10);

if (!appId || !appKey || !appSecret) {
  console.error('REVERB_APP_ID / KEY / SECRET missing from .env');
  process.exit(2);
}

const stats = { connected: 0, subscribed: 0, connectMs: [], received: 0, latencies: [] };
const clients = [];
let finished = false;

function sendPusherEvent(payload) {
  const body = JSON.stringify(payload);
  const bodyMd5 = crypto.createHash('md5').update(body).digest('hex');
  const timestamp = Math.floor(Date.now() / 1000);
  const query = `auth_key=${appKey}&auth_timestamp=${timestamp}&auth_version=1.0&body_md5=${bodyMd5}`;
  const toSign = `POST\n/apps/${appId}/events\n${query}`;
  const signature = crypto.createHmac('sha256', appSecret).update(toSign).digest('hex');
  const fullPath = `/apps/${appId}/events?${query}&auth_signature=${signature}`;

  return new Promise((resolve, reject) => {
    const req = http.request(
      { host, port, path: fullPath, method: 'POST', headers: { 'Content-Type': 'application/json', 'Content-Length': Buffer.byteLength(body) } },
      (res) => { res.resume(); res.on('end', () => resolve(res.statusCode)); }
    );
    req.on('error', reject);
    req.end(body);
  });
}

function avg(a) { return a.length ? Math.round(a.reduce((x, y) => x + y, 0) / a.length) : 0; }
function pct(a, p) { if (!a.length) return 0; const s = [...a].sort((x, y) => x - y); return s[Math.min(s.length - 1, Math.floor(p * s.length))]; }

function finish() {
  if (finished) return;
  finished = true;

  const expected = connections * eventCount;

  console.log('');
  console.log('=== Reverb load test ===');
  console.log(`connections:            ${connections}`);
  console.log(`connected:              ${stats.connected}`);
  console.log(`subscribed:             ${stats.subscribed}`);
  console.log(`connect time (avg/p95): ${avg(stats.connectMs)} ms / ${pct(stats.connectMs, 0.95)} ms`);
  console.log(`events sent:            ${eventCount}`);
  console.log(`deliveries:             ${stats.received}/${expected} (${expected ? Math.round(100 * stats.received / expected) : 0}%)`);
  console.log(`latency (avg/p95/max):  ${avg(stats.latencies)} ms / ${pct(stats.latencies, 0.95)} ms / ${stats.latencies.length ? Math.max(...stats.latencies) : 0} ms`);

  for (const c of clients) { try { c.disconnect(); } catch (e) {} }

  const ok = stats.connected === connections && stats.subscribed === connections && stats.received === expected;
  console.log(ok ? 'RESULT: PASS' : 'RESULT: CHECK (see numbers above)');
  process.exit(ok ? 0 : 1);
}

async function fireEvents() {
  try {
    for (let n = 0; n < eventCount; n++) {
      const payload = { name: eventName, channel: 'subsync', data: JSON.stringify({ sentAt: Date.now(), n }) };
      const status = await sendPusherEvent(payload);
      if (status !== 200) console.error(`event ${n}: HTTP ${status}`);
      await new Promise((r) => setTimeout(r, 200));
    }
    setTimeout(finish, 1500);
  } catch (e) {
    console.error('failed to send events:', e.message);
    finish();
  }
}

for (let i = 0; i < connections; i++) {
  const client = new Pusher(appKey, {
    wsHost: host, wsPort: port, forceTLS: false,
    enabledTransports: ['ws'], cluster: 'mt1', disableStats: true,
  });
  clients.push(client);

  const t0 = Date.now();
  client.connection.bind('connected', () => {
    stats.connected++;
    stats.connectMs.push(Date.now() - t0);

    const ch = client.subscribe('subsync');
    ch.bind('pusher:subscription_succeeded', () => {
      stats.subscribed++;
      if (stats.subscribed === connections) setTimeout(fireEvents, 300);
    });
    ch.bind(eventName, (data) => {
      stats.received++;
      if (data && data.sentAt) stats.latencies.push(Date.now() - data.sentAt);
    });
  });

  client.connection.bind('error', (e) => console.error(`client ${i} error:`, e && e.error ? e.error : e));
  client.connection.bind('unavailable', () => console.error(`client ${i}: reverb unavailable`));
}

setTimeout(() => {
  if (finished) return;
  console.error(`timeout: connected=${stats.connected}/${connections} subscribed=${stats.subscribed}/${connections}`);
  finish();
}, 30000).unref();
