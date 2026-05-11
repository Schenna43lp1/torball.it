const express = require('express');
const http = require('http');
const WebSocket = require('ws');
const { createClient } = require('redis');

const app = express();
const server = http.createServer(app);

const wss = new WebSocket.Server({
    server,
    maxPayload: 1024 * 16
});

const BROADCAST_SECRET = process.env.WS_BROADCAST_SECRET || 'CHANGE_ME';
const REDIS_URL = process.env.REDIS_URL || 'redis://redis:6379';

const redis = createClient({
    url: REDIS_URL
});

const subscriber = redis.duplicate();

const rooms = new Map();

app.disable('x-powered-by');
app.use(express.json({ limit: '16kb' }));

async function initRedis() {
    await redis.connect();
    await subscriber.connect();

    await subscriber.subscribe('live_scores', (message) => {
        try {
            const payload = JSON.parse(message);
            broadcast(payload);
        } catch (e) {
            console.error(e);
        }
    });
}

function verifySecret(req, res, next) {
    const secret = req.headers['x-broadcast-secret'];

    if (!secret || secret !== BROADCAST_SECRET) {
        return res.status(403).json({ error: 'Forbidden' });
    }

    next();
}

function broadcast(payload, room = null) {
    const data = JSON.stringify(payload);

    wss.clients.forEach((client) => {
        if (client.readyState !== WebSocket.OPEN) return;

        if (room) {
            const clientRoom = rooms.get(client);
            if (clientRoom !== room) return;
        }

        client.send(data);
    });
}

wss.on('connection', (ws, req) => {
    console.log('Client connected:', req.socket.remoteAddress);

    ws.send(JSON.stringify({
        type: 'system',
        message: 'Connected to Torball Live WebSocket'
    }));

    ws.on('message', (raw) => {
        try {
            const data = JSON.parse(raw.toString());

            if (data.type === 'join_room') {
                rooms.set(ws, data.room);

                ws.send(JSON.stringify({
                    type: 'room_joined',
                    room: data.room
                }));
            }
        } catch (e) {
            console.error(e);
        }
    });

    ws.on('close', () => {
        rooms.delete(ws);
        console.log('Client disconnected');
    });
});

app.post('/broadcast', verifySecret, async (req, res) => {

    const payload = {
        type: String(req.body.type || 'ticker').slice(0, 30),
        message: String(req.body.message || '').slice(0, 1000),
        room: req.body.room || null,
        created_at: new Date().toISOString()
    };

    await redis.publish('live_scores', JSON.stringify(payload));

    res.json({ success: true });
});

initRedis().then(() => {
    server.listen(3001, '0.0.0.0', () => {
        console.log('Torball WebSocket server running on port 3001');
    });
});
