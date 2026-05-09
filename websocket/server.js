const express = require('express');
const http = require('http');
const WebSocket = require('ws');

const app = express();
const server = http.createServer(app);

const wss = new WebSocket.Server({
    server,
    maxPayload: 1024 * 16
});

const BROADCAST_SECRET = process.env.WS_BROADCAST_SECRET || 'CHANGE_ME';

app.disable('x-powered-by');
app.use(express.json({ limit: '16kb' }));

function verifySecret(req, res, next) {
    const secret = req.headers['x-broadcast-secret'];

    if (!secret || secret !== BROADCAST_SECRET) {
        return res.status(403).json({ error: 'Forbidden' });
    }

    next();
}

wss.on('connection', (ws, req) => {
    console.log('Client connected:', req.socket.remoteAddress);

    ws.send(JSON.stringify({
        type: 'system',
        message: 'Connected to Torball Live WebSocket'
    }));

    ws.on('message', () => {
        // Block incoming client messages
    });

    ws.on('close', () => {
        console.log('Client disconnected');
    });
});

app.post('/broadcast', verifySecret, (req, res) => {

    const payload = {
        type: String(req.body.type || 'ticker').slice(0, 30),
        message: String(req.body.message || '').slice(0, 1000),
        created_at: new Date().toISOString()
    };

    const data = JSON.stringify(payload);

    wss.clients.forEach((client) => {
        if (client.readyState === WebSocket.OPEN) {
            client.send(data);
        }
    });

    res.json({ success: true });
});

server.listen(3001, '0.0.0.0', () => {
    console.log('Torball WebSocket server running on port 3001');
});
