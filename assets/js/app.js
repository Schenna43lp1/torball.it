const navToggle = document.querySelector('.nav-toggle');
const navLinks = document.querySelector('#nav-links');
const themeToggle = document.querySelector('.theme-toggle');

if (navToggle && navLinks) {
    navToggle.addEventListener('click', () => {
        navLinks.classList.toggle('active');
    });
}

if (localStorage.getItem('theme') === 'dark') {
    document.body.classList.add('darkmode');
}

if (themeToggle) {
    themeToggle.addEventListener('click', () => {
        document.body.classList.toggle('darkmode');
        localStorage.setItem(
            'theme',
            document.body.classList.contains('darkmode') ? 'dark' : 'light'
        );
    });
}

if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/service-worker.js');
}

let deferredPrompt;

window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;

    const installBtn = document.createElement('button');
    installBtn.innerText = '📲 App installieren';
    installBtn.className = 'install-btn';

    installBtn.onclick = async () => {
        installBtn.remove();
        deferredPrompt.prompt();
        await deferredPrompt.userChoice;
        deferredPrompt = null;
    };

    document.body.appendChild(installBtn);
});

async function refreshLiveData() {
    if (typeof loadTable === 'function') {
        loadTable();
    }

    if (typeof loadMatches === 'function') {
        loadMatches();
    }
}

setInterval(refreshLiveData, 15000);

const wsProtocol = location.protocol === 'https:' ? 'wss://' : 'ws://';
const ws = new WebSocket(`${wsProtocol}${location.host}/ws`);

ws.onmessage = () => {
    refreshLiveData();

    if (Notification.permission === 'granted') {
        new Notification('Torball Update', {
            body: 'Neue Ergebnisse oder Tore verfügbar.'
        });
    }
};

if ('Notification' in window && Notification.permission !== 'granted') {
    Notification.requestPermission();
}
