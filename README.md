### Описание

Синхронизация запускается каждый день в 12:00 и в 18:00
Время можно изменить в `Kernel.php`

Scheduler запускает команду `sync:all` для синхронизации данных для всех аккаунтов. `sync:all` в свою очередь запускает остальные команды,
которые диспатчат Job'ы.

Scheduler и воркер стартует вместе с контейнером через `start.sh`.

### Команды

Просто скопируйте команды чтобы быстро начать
```bash
php artisan company:add nike
php artisan account:add 1 account_name
php artisan token-type:add api-key
php artisan api-service:add wb-reports-api http://109.73.206.144:6969 api-key
php artisan api-token:add 1 wb-reports-api api-key E6kUTYrYwZq2tN4QEtyzsbEBk3ie
```

Команды можно запустить и вручную:

```bash
php artisan sync:incomes
php artisan sync:orders
php artisan sync:sales
# можно указать указать id аккаунта
php artisan sync:stocks 1
```
Или одной командой
```bash
php artisan sync:all
# можно указать id аккаунта
php artisan sync:all 1
```

Прогресс выполнения виден в терминале.

Список таблиц:
- incomes
- orders
- stocks
- sales
- companies
- accounts
- api_services
- token_types
- api_service_token_type
- api_tokens
- jobs
- failed_jobs

