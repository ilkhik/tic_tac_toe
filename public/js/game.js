window.onload = () => {
    const game = new Game();
};

class Game {
    #board;
    #server;
    #user_info;
    #status;
    
    constructor() {
        document.getElementById('logout').onclick = async () => {
            await this.#server.logout();
            localStorage.removeItem('token');
            localStorage.removeItem('refreshToken');
            location.pathname = '/login';
        };
        this.#board = new Board(async clickedCell => {
            if (this.#status.your_turn) {
                this.#status = await this.#server.turn(clickedCell);
                this.#board.cells = this.#status.board;
                this.#board.updateUI();
                this.updateStatusUI();
            }
        });
        this.#server = new Server();
        
        this.updateUserInfo();
        this.updateStatus();
        setInterval(() => {
            this.updateStatus();
            this.updateUserInfo();
        }, 3000);
        document.getElementById('start-new-game').onclick = () => this.startNewGame();
    }
    
    updateUserInfo() {
        this.#server.getUserInfo().then((data) => {
            this.#user_info = data;
            this.updateUserInfoUI();
        });
    }
    
    updateUserInfoUI() {
        document.getElementById('username').innerHTML = this.#user_info.username;
        document.getElementById('victory-count').innerHTML = this.#user_info.victories;
        document.getElementById('defeat-count').innerHTML = this.#user_info.defeats;
    }
    
    async updateStatus() {
        const data = await this.#server.getStatus();
        this.#status = data;
        this.#board.cells = this.#status.board;
        this.#board.updateUI();
        this.updateStatusUI();
    }
    
    updateStatusUI() {
        // TODO
        let text = '';
        const boardElement = document.getElementById('board');
        if (this.#status.status === 'waiting') {
            boardElement.style.display = 'none';
            text = 'Ожидаем присоединения соперника';
        } else if (this.#status.status === 'game_over') {
            boardElement.style.display = 'flex';
            document.getElementById('start-new-game').hidden = false;
            if (this.#status.winner === this.#user_info.id) {
                text = 'Вы победили!';
            } else if (this.#status.winner !== null) {
                text = 'Вы проиграли!';
            } else {
                text = 'Ничья!';
            }
        } else {
            boardElement.style.display = 'flex';
            const whoIsMoving = this.#status.your_turn ? 'Ваш ход. ' : 'Ждём хода соперника. ';
            const mySign = (this.#status.your_sign === 'cross') ? 
                                'Вы ходите крестиком. ' : 
                                'Вы ходите ноликом.';
            text = whoIsMoving + mySign;
        }
        document.getElementById('status').innerHTML = text;
    }
    
    async startNewGame() {
        const data = await this.#server.startNewGame();
        this.#status = data;
        this.#board.cells = this.#status.board;
        this.#board.updateUI();
        this.updateStatusUI();
        document.getElementById('start-new-game').hidden = true;
    }
}

class Board {
    #boardElement;
    #cellElements;
    cells;
    
    constructor(clickToCellCallback) {
        this.#boardElement = document.getElementById('board');
        this.#cellElements = this.#boardElement.children;
        this.cells = Array(9).fill(0);
        for (let i = 0; i < 9; i++) {
            this.#cellElements[i].onclick = () => {
                clickToCellCallback(i);
            };
        }
    }
    
    setVisible(visible) {
        this.#boardElement.style.display = visible ? 'flex' : 'none';
    }
    
    clear() {
        this.cells.fill(0);
        this.updateUI();
    }
    
    updateUI() {
        for (let i = 0; i < 9; i++) {
            let ch;
            switch (this.cells[i]) {
                case 0:
                    ch = '';
                    break;
                case 1:
                    ch = 'X';
                    break;
                case 2:
                    ch = '0';
                    break;
            }
            this.#cellElements[i].innerHTML = ch;
        }
    }
}

class Server {
    #token;
    #refreshed;
    #refreshing;
    
    constructor() {
        this.#token = localStorage.getItem('token');
    }
    
    getStatus() {
        return this.#sendRequest('GET', '/api/game/status');
    }
    
    getUserInfo() {
        return this.#sendRequest('GET', '/api/game/user_info');
    }
    
    logout() {
        return this.#sendRequest('POST', '/api/logout', {
            refresh: localStorage.getItem('refreshToken')
        });
    }
    
    turn(ceil) {
        return this.#sendRequest('POST', '/api/game/move', {ceil});
    }
    
    startNewGame() {
        return this.#sendRequest('POST', '/api/game/start');
    }
    
    async #sendRequest(method, uri, data) {
        const response = await this.#fetch(method, uri, data);
        
        const responseData = await response.json();
        if (response.ok) {
            return responseData;
        } else if (response.status === 401){
            await this.#refreshToken();
            const responseAttempt = await this.#fetch(method, uri, data);
            const responseAttemptData = await responseAttempt.json();
            if (responseAttempt.ok) {
                return responseAttemptData;
            } else {
                throw responseAttemptData.message ?? 'Fetch error';
            }
        } else {
            throw responseData.message ?? 'Fetch error';
        }
    }
    
    async #fetch(method, uri, data) {
        return await fetch(uri, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                token: this.#token
            },
            body: JSON.stringify(data)
        });
    }
    
    async #refreshToken() {
        while (this.#refreshing) {
            await sleep(100);
            return;
        }
        if (this.#refreshed && Date.now() - this.#refreshed < 1000*60*2) {
            return;
        }
        this.#refreshing = true;
        
        const response = await fetch('/api/refresh_token', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                refresh: localStorage.getItem('refreshToken')
            })
        });
        if (response.ok) {
            const responseData = await response.json();
            localStorage.setItem('token', responseData.token);
            localStorage.setItem('refreshToken', responseData.refresh);
            this.#token = responseData.token;
            this.#refreshed = Date.now();
        } else {
            location.pathname = '/login';
        }
        this.#refreshing = false;
    }
}

async function sleep(ms) {
    return new Promise(resolve => setTimeout(() => resolve()), ms);
}