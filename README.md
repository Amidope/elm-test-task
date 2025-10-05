### Данные для доступа
<details>
  <summary>Спойлер</summary>

- DB_HOST=a1175940.xsph.ru  
- DB_PORT=3306  
- DB_DATABASE=a1175940_eml  
- DB_USERNAME=a1175940_readonly_user  
- DB_PASSWORD=SR4r5NaZXJYY3nDY7Dm4jEW94

</details>

Список таблиц:
- incomes
- orders
- stocks
- sales

## Описание
для доступа к api, скопировать из `env.example` в `.env` и заполнить:
```
API_IP
API_PORT
API_KEY
```
Скачивание данных запускается командами:
```bash
php artisan sync:incomes
php artisan sync:orders
php artisan sync:sales
php artisan sync:stocks
```
Или одной командой
```bash
php artisan sync:all
```

Прогресс выполнения виден в терминале.


<details>
  <summary>Спойлер</summary>
Т.к. в записи из api невозможно однозначно идентифицировать записи, проверка на уникальность невозможна (например `od_id` в талбице `orders` почему-то всегда со значением `0`). Из-за этого, значения в таблицы вставляются без проверки на уникальность и если при скачивании произойдет сбой, то скачивание нужно будет начать сначала.
Но при ошибке `429 Too Many Attempts` скрипт отработает нормально.

P.S. Бесплатные хостинги это ужас.
</details>
