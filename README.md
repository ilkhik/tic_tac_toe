# Деплой
Копируем файл **env** в **.env**
```
cp env .env
```
В **.env** вводим настройки подключения к БД. Запуск на порту 8080:
```
php spark serve
```
## Websockets
Для вебсокетов используется **centrifugo**
```
ws/centrifugo -c ws/config.json # В Linux

ws/centrifugo.exe -c ws/config.json :: В Windows
```