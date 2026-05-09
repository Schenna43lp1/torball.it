const express = require('express');
const http = require('http');
const WebSocket = require('ws');

const app = express();
const server = http.createServer(app);
const wss = new WebSocket.Server({ server });

app.use(express.json());

wss.on('connection', (ws) => {
    console.log('Client connected');

    ws.send(JSON.stringify({
        type: 'system',
        message: 'Connected to Torball Live WebSocket'
    }));

    ws.on('close', () => {
        console.log('Client disconnected');
    });
});

app.post('/broadcast', (req, res) => {
    const payload = req.body;

    const data = JSON.stringify(payload);

    wss.clients.forEach((client) => {
        if (client.readyState === WebSocket.OPEN) {
            client.send(data);
        }
    });

    res.json({ success: true });
});

server.listen(3001, () => {
    console.log('Torball WebSocket server running on port 3001');
});
