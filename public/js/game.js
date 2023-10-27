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
        this.#server = new Server(userInfo => {
            this.#user_info = userInfo;
            this.updateUserInfoUI();
        }, status => {
            this.#status = status;
            this.#board.cells = this.#status.board;
            this.#board.updateUI();
            this.updateStatusUI();
        });
        
        this.updateUserInfo();
        this.updateStatus();
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
                                'Вы ходите ноликом. ';
            const enemyUsername = this.#status.enemy 
                                        ? `Соперник: ${this.#status.enemy.username}` 
                                        : '';
            text = whoIsMoving + mySign + enemyUsername;
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
    /**
     * 
     * @type Boolean
     */
    #refreshing;
    /**
     * 
     * @type function
     * @param {object} userInfo 
     */
    #userInfoUpdateCallback;
    /**
     * 
     * @type function
     * @param {Object} status 
     */
    #statusUpdateCallback;
    #wxEnabled;
    /**
     * 
     * @type array
     */
    #timers = [];
    
    constructor(userInfoUpdateCallback, statusUpdateCallback) {
        this.#token = localStorage.getItem('token');
        this.#userInfoUpdateCallback = userInfoUpdateCallback;
        this.#statusUpdateCallback = statusUpdateCallback;
        this.startListening();
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
    
    async startListening() {
        this.startListeningWs();
        this.changeMode(false);
    }
    
    changeMode(wsEnabled) {
        for (const timer of this.#timers) {
            clearTimeout(timer);
        }
        this.#timers = [];
        if (!wsEnabled) {
            if (this.#userInfoUpdateCallback) {
                this.#timers.push(setInterval(async () => {
                    const userInfo = await this.getUserInfo();
                    this.#userInfoUpdateCallback(userInfo);
                }, 2000));
            }
            if (this.#statusUpdateCallback) {
                this.#timers.push(setInterval(async () => {
                    const status = await this.getStatus();
                    this.#statusUpdateCallback(status);
                }, 2000));
            }
        }
    }
    
    async startListeningWs() {
        while (!this.#token) {
            await sleep(50);
        }
        const token = await this.#sendRequest('GET', '/api/ws_jwt');
        const centrifuge = new Centrifuge(`ws://${location.hostname}:31492/connection/websocket`, {
          token: token.token
        });
        
        centrifuge.on('connecting', function (ctx) {
          console.log(`connecting: ${ctx.code}, ${ctx.reason}`);
        }).on('connected', function (ctx) {
          console.log(`connected over ${ctx.transport}`);
        }).on('disconnected', ctx => {
          console.log(`disconnected: ${ctx.code}, ${ctx.reason}`);
          this.changeMode(false);
        }).connect();

        const sub = centrifuge.newSubscription(`user#${token.id}`);

        sub.on('publication', (ctx) => {
          console.log(ctx.data);
          const data = ctx.data;
          switch (data.action) {
              case 'updateStatus':
                  this.#statusUpdateCallback(data.data);
                  break;
              case 'updateUserInfo':
                  this.#userInfoUpdateCallback(data.data);
                  break;
          }
        }).on('subscribing', function (ctx) {
          console.log(`subscribing: ${ctx.code}, ${ctx.reason}`);
        }).on('subscribed', ctx => {
          console.log('subscribed', ctx);
          this.changeMode(true);
        }).on('unsubscribed', function (ctx) {
          console.log(`unsubscribed: ${ctx.code}, ${ctx.reason}`);
        }).subscribe();
    }
}

async function sleep(ms) {
    return new Promise(resolve => setTimeout(() => resolve()), ms);
}