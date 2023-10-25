window.onload = () => {
    const game = new Game();
};

class Game {
    #board;
    #server;
    #user_info;
    #status;
    
    constructor() {
        this.#board = new Board();
        this.#server = new Server();
        
        this.updateUserInfo();
        this.updateStatus();
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
    
    updateStatus() {
        this.#server.getStatus().then((data) => {
            this.#status = data;
            this.#board.cells = this.#status.board;
            this.#board.updateUI();
            this.updateStatusUI();
        });
    }
    
    updateStatusUI() {
        // TODO
        let text = '';
        if (this.#status.status === 'waiting') {
            text = 'Ожидаем присоединения соперника';
        } else if (this.#status.status === 'game_over') {
            if (this.#status.winner === this.#user_info.id) {
                text = 'Вы победили!';
            } else if (this.#status.winner !== null) {
                text = 'Вы проиграли!';
            } else {
                text = 'Ничья!';
            }
        } else {
            const whoIsMoving = this.#status.your_turn ? 'Ваш ход. ' : 'Ждём хода соперника. ';
            const mySign = (this.#status.your_sign === 'cross') ? 
                                'Вы ходите крестиком. ' : 
                                'Вы ходите ноликом';
            text = whoIsMoving + mySign;
        }
        document.getElementById('status').innerHTML = text;
    }
}

class Board {
    #boardElement;
    #cellElements;
    cells;
    
    constructor() {
        this.#boardElement = document.getElementById('board');
        this.#cellElements = this.#boardElement.children;
        this.cells = Array(9).fill(0);
        for (let i = 0; i < 9; i++) {
            this.#cellElements[i].onclick = () => {
                this.clickCell(i);
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
    
    clickCell(n) {
        // TODO
        console.log(`clicked: ${n}`);
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
        this.#token = sessionStorage.getItem('token');
    }
    
    getStatus() {
        return this.#sendRequest('GET', '/api/game/status');
    }
    
    getUserInfo() {
        return this.#sendRequest('GET', '/api/game/user_info');
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
        if (this.#refreshing) {
            do {
                await new Promise((resolve) => {
                    setTimeout(() => resolve(), 500)
                });
            } while (this.#refreshing);
            return;
        }
        if (Date.now() - this.#refreshed < 1000*60*2 || this.#refreshing) {
            return;
        }
        this.#refreshing = true;
        
        const response = await fetch('/api/refresh_token', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                refresh: sessionStorage.getItem('refreshToken')
            })
        });
        if (response.ok) {
            const responseData = await response.json();
            sessionStorage.setItem('token', responseData.token);
            sessionStorage.setItem('refreshToken', responseData.refresh);
            this.#token = responseData.token;
            this.#refreshed = Date.now();
        } else {
            location.pathname = '/login';
        }
        this.#refreshing = false;
    }
}
