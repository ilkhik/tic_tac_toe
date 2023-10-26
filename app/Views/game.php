<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="css/game.css" rel="stylesheet" type="text/css" />
        <script src="/js/game.js"></script>
        <title>Крестики нолики</title>
    </head>
    <body>
        <div class="content">
            <div id="logout">Выйти</div>
            <div id="user-info">
                Имя пользователя: <span id="username"></span><br>
                Количество побед: <span id="victory-count"></span><br>
                Количество поражений: <span id="defeat-count"></span>
            </div>
            <div id="status"></div>
            <div id="start-new-game" hidden>Начать новую игру</div>
            <div class="board" id="board">
                <button class="cell"></button>
                <button class="cell"></button>
                <button class="cell"></button>
                <button class="cell"></button>
                <button class="cell"></button>
                <button class="cell"></button>
                <button class="cell"></button>
                <button class="cell"></button>
                <button class="cell"></button>
            </div>
        </div>
    </body>
</html>
