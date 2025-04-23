# План Задач: Система Учета ACNE INC

Этот документ содержит пошаговый план задач для разработки системы учета ACNE INC на `Laravel 12`. 

## Этап 1: Настройка Проекта и Окружения

1.  [X] **Задача 1.1**: Установить последнюю стабильную версию Laravel с помощью Composer (`composer create-project laravel/laravel acne-accounting`).
2.  [X] **Задача 1.2**: Перейти в директорию проекта (`cd acne-accounting`).
3.  [X] **Задача 1.3**: Сконфигурировать файл `.env` (Название приложения, URL, Данные для подключения к БД MySQL).
4.  [X] **Задача 1.4**: Создать базу данных MySQL, указанную в `.env`.
5.  [X] **Задача 1.5**: Установить пакет Laravel Breeze (`composer require laravel/breeze --dev`).
6.  [X] **Задача 1.6**: Установить скаффолдинг Breeze с Blade (`php artisan breeze:install blade`).
7.  [X] **Задача 1.7**: Установить зависимости NPM (`npm install`).
8.  [X] **Задача 1.8**: Собрать фронтенд-ассеты (`npm run build`).
9.  [X] **Задача 1.9**: Запустить миграции Breeze (`php artisan migrate`) для создания таблиц `users`, `password_reset_tokens`, `failed_jobs`, `personal_access_tokens`.
10. [X] **Задача 1.10**: Проверить конфигурацию Tailwind CSS, установленную Breeze (например, `tailwind.config.js`, `postcss.config.js`, `vite.config.js`, `resources/css/app.css`).
11. [X] **Задача 1.11**: Убедиться, что фронтенд-ассеты с Tailwind CSS успешно собраны (выполнено в Задаче 1.8).
12. [ ] **Задача 1.12**: Проверить базовую установку и стандартную аутентификацию Laravel (Регистрация, Вход, Выход).

## Этап 2: Миграции Базы Данных

*Примечание: Для всех миграций использовать типы данных и ограничения, указанные в `tech.md`.* 

13. [X] **Задача 2.1**: Создать миграцию для таблицы `teams` (`php artisan make:migration create_teams_table`).
14. [X] **Задача 2.2**: Описать структуру таблицы `teams` в файле миграции.
15. [X] **Задача 2.3**: Изменить существующую миграцию `users` (добавленную Breeze), добавив поля: `telegram_id`, `role`, `team_id` (с внешним ключом к `teams`), `sub2` (JSON), `active`, `is_virtual`, `contact_info`.
16. [X] **Задача 2.4**: Создать миграцию для таблицы `accounts` (`php artisan make:migration create_accounts_table`).
17. [X] **Задача 2.5**: Описать структуру таблицы `accounts` в файле миграции (включая внешний ключ `user_id` к `users`).
18. [X] **Задача 2.6**: Создать миграцию для таблицы `daily_expenses` (`php artisan make:migration create_daily_expenses_table`).
19. [X] **Задача 2.7**: Описать структуру таблицы `daily_expenses` в файле миграции (включая внешние ключи `buyer_id`, `created_by` к `users`).
20. [X] **Задача 2.8**: Создать миграцию для таблицы `adjustments` (`php artisan make:migration create_adjustments_table`).
21. [X] **Задача 2.9**: Описать структуру таблицы `adjustments` в файле миграции (включая внешние ключи `buyer_id`, `created_by` к `users`).
22. [X] **Задача 2.10**: Создать миграцию для таблицы `fund_transfers` (`php artisan make:migration create_fund_transfers_table`).
23. [X] **Задача 2.11**: Описать структуру таблицы `fund_transfers` в файле миграции (включая внешние ключи `from_account_id`, `to_account_id` к `accounts`, `created_by` к `users`).
24. [X] **Задача 2.12**: Создать миграцию для таблицы `transactions` (`php artisan make:migration create_transactions_table`).
25. [X] **Задача 2.13**: Описать структуру таблицы `transactions` в файле миграции (включая полиморфные колонки `operation_id`, `operation_type`, индексы).
26. [X] **Задача 2.14**: Создать миграцию для таблицы `transaction_lines` (`php artisan make:migration create_transaction_lines_table`).
27. [X] **Задача 2.15**: Описать структуру таблицы `transaction_lines` в файле миграции (включая внешние ключи `transaction_id`, `account_id`, индексы, проверку `chk_debit_credit`).
28. [X] **Задача 2.16**: Создать миграцию для таблицы `income_records` (`php artisan make:migration create_income_records_table`).
29. [X] **Задача 2.17**: Описать структуру таблицы `income_records` в файле миграции.
30. [X] **Задача 2.18**: Запустить все созданные миграции (`php artisan migrate`).

## Этап 3: Модели Eloquent и Отношения

31. [X] **Задача 3.1**: Создать модель `Team` (`php artisan make:model Team`). Определить отношение `users()` (hasMany).
32. [X] **Задача 3.2**: Настроить модель `User` (добавлена Breeze): добавить `$fillable` поля, добавить приведение `sub2` к `json/array`, определить отношение `team()` (belongsTo, nullable), `accounts()` (hasMany), `dailyExpenses()` (hasMany), `adjustments()` (hasMany).
33. [X] **Задача 3.3**: Создать модель `Account` (`php artisan make:model Account`). Определить отношения `user()` (belongsTo, nullable), `transactionLines()` (hasMany). Добавить метод `getBalance()` (или аксессор).
34. [X] **Задача 3.4**: Создать модель `DailyExpense` (`php artisan make:model DailyExpense`). Определить отношения `buyer()` (belongsTo User), `creator()` (belongsTo User), `transaction()` (morphOne).
35. [X] **Задача 3.5**: Создать модель `Adjustment` (`php artisan make:model Adjustment`). Определить отношения `buyer()` (belongsTo User), `creator()` (belongsTo User), `transaction()` (morphOne).
36. [X] **Задача 3.6**: Создать модель `FundTransfer` (`php artisan make:model FundTransfer`). Определить отношения `fromAccount()` (belongsTo Account), `toAccount()` (belongsTo Account), `creator()` (belongsTo User), `transaction()` (morphOne).
37. [X] **Задача 3.7**: Создать модель `Transaction` (`php artisan make:model Transaction`). Определить отношения `lines()` (hasMany), `operation()` (morphTo).
38. [X] **Задача 3.8**: Создать модель `TransactionLine` (`php artisan make:model TransactionLine`). Определить отношения `transaction()` (belongsTo), `account()` (belongsTo).
39. [X] **Задача 3.9**: Создать модель `IncomeRecord` (`php artisan make:model IncomeRecord`).

## Этап 4: Основная Логика (Сервисы/Наблюдатели)

40. [X] **Задача 4.1**: Создать сервисы `AccountService` (для поиска счетов) и `TransactionService` (для записи транзакций двойной записи).
41. [X] **Задача 4.2**: Создать наблюдатели (Observers) для моделей `DailyExpense`, `Adjustment`, `FundTransfer`.
42. [X] **Задача 4.3**: Зарегистрировать наблюдателей в `EventServiceProvider` и добавить провайдер в `config/app.php`.
43. [ ] **Задача 4.4**: Реализовать метод `recordOperationTransaction` в `TransactionService` для атомарного создания `Transaction` и связанных `TransactionLine` (дебет/кредит), принимая ID счетов и суммы.
44. [ ] **Задача 4.5**: В `DailyExpenseObserver` (метод `created`), используя `AccountService` и `TransactionService`, инициировать создание транзакции (дебет счета баера, кредит счета агентства/источника - *уточнить логику кредита*).
45. [ ] **Задача 4.6**: В `AdjustmentObserver` (метод `created`), используя `AccountService` и `TransactionService`, инициировать создание транзакции (дебет/кредит счета баера, кредит/дебет счета агентства, с учетом `accounting_period`).
46. [ ] **Задача 4.7**: В `FundTransferObserver` (метод `created`), используя `AccountService` и `TransactionService`, инициировать создание транзакции (дебет счета получателя, кредит счета отправителя).
47. [ ] **Задача 4.8**: Реализовать логику автоматического создания `Account` (тип `BUYER_MAIN`, валюта USD) при создании `User` с ролью `buyer` (например, в `UserObserver` или `AccountService`).
48. [ ] **Задача 4.9**: Реализовать логику автоматического создания `Account` (тип `AGENCY`, валюта USD) при создании `User` с ролью `agency` (например, в `UserObserver` или `AccountService`).

## Этап 5: Аутентификация и Авторизация

49. [ ] **Задача 5.1**: Настроить веб-аутентификацию (формы входа/регистрации уже должны быть от Breeze).
50. [ ] **Задача 5.2**: Реализовать аутентификацию через Telegram (например, добавить маршрут, контроллер `TelegramAuthController`, использовать Socialite или HTTP-клиент для взаимодействия с Telegram API, связать/создать пользователя по `telegram_id`).
51. [ ] **Задача 5.3**: Определить роли пользователей (можно использовать ENUM в модели `User` или пакет для управления ролями, например, `spatie/laravel-permission`).
52. [ ] **Задача 5.4**: Создать `AuthServiceProvider` и определить Gates или Policies для контроля доступа:
    *   `TeamPolicy`: Управление командами (только Owner).
    *   `UserPolicy`: Управление пользователями (только Owner).
    *   `DailyExpensePolicy`: Создание/Редактирование (Buyer - только свои, Finance/Owner - все), Просмотр (Buyer - свои, Finance/Owner - все).
    *   `AdjustmentPolicy`: Создание/Редактирование (Finance/Owner), Просмотр (Finance/Owner, Buyer - ?).
    *   `FundTransferPolicy`: Создание/Редактирование (Finance/Owner), Просмотр (Finance/Owner).
    *   `AccountPolicy`: Просмотр (Owner/Finance - все, Buyer - свой).
53. [ ] **Задача 5.5**: Применить middleware и проверки авторизации в контроллерах и маршрутах.

## Этап 6: UI и Контроллеры - Роль Owner

54. [ ] **Задача 6.1**: Создать `TeamController` (`php artisan make:controller TeamController --resource`). Реализовать CRUD-методы с использованием `TeamPolicy`.
55. [ ] **Задача 6.2**: Создать Blade-шаблоны для CRUD операций с командами (`teams/index.blade.php`, `teams/create.blade.php`, `teams/edit.blade.php`).
56. [ ] **Задача 6.3**: Создать `UserController` (частично существует от Breeze). Дополнить методами для управления пользователями (index, edit, update, destroy, invite?) с использованием `UserPolicy`.
57. [ ] **Задача 6.4**: Создать Blade-шаблоны для управления пользователями (`users/index.blade.php`, `users/edit.blade.php`).
58. [ ] **Задача 6.5**: Создать контроллер `DashboardController`. Реализовать метод `ownerDashboard()`.
59. [ ] **Задача 6.6**: Создать Blade-шаблон для панели управления Владельца (`dashboards/owner.blade.php`).
60. [ ] **Задача 6.7**: Создать `AccountController`. Реализовать метод `index()` для Владельца (отображение всех счетов).
61. [ ] **Задача 6.8**: Создать Blade-шаблон для списка счетов (`accounts/index.blade.php`).
62. [ ] **Задача 6.9**: Настроить маршруты (`routes/web.php`) для Owner (управление командами, пользователями, просмотр счетов, доступ ко всем операциям, отчеты).

## Этап 7: UI и Контроллеры - Роль Finance

63. [ ] **Задача 7.1**: Создать `DailyExpenseController` (`php artisan make:controller DailyExpenseController --resource`). Реализовать CRUD-методы с учетом прав Finance (может редактировать все).
64. [ ] **Задача 7.2**: Создать Blade-шаблоны для CRUD операций с ежедневными расходами (`daily_expenses/index.blade.php`, `daily_expenses/create.blade.php`, `daily_expenses/edit.blade.php`). Форма должна позволять выбирать баера.
65. [ ] **Задача 7.3**: Создать `AdjustmentController` (`php artisan make:controller AdjustmentController --resource`). Реализовать CRUD-методы.
66. [ ] **Задача 7.4**: Создать Blade-шаблоны для CRUD операций с корректировками (`adjustments/index.blade.php`, `adjustments/create.blade.php`, `adjustments/edit.blade.php`). Форма должна позволять выбирать баера и период.
67. [X] **Задача 7.5**: Создать `FundTransferController` (`php artisan make:controller FundTransferController --resource`). Реализовать CRUD-методы. *(Partially Done: Created Controller, implemented create/store/getAccounts)*
68. [X] **Задача 7.6**: Создать Blade-шаблоны для CRUD операций с перемещением средств (`fund_transfers/index.blade.php`, `fund_transfers/create.blade.php`, `fund_transfers/edit.blade.php`). Форма должна позволять выбирать счета. *(Partially Done: Created create view)*
69. [ ] **Задача 7.7**: Реализовать метод `financeDashboard()` в `DashboardController`.
70. [ ] **Задача 7.8**: Создать Blade-шаблон для панели управления Финансиста (`dashboards/finance.blade.php`).
71. [ ] **Задача 7.9**: Настроить маршруты для Finance (доступ к операциям, просмотр пользователей/команд/счетов, отчеты). *(Partially Done: Added FundTransfer & Transaction routes)*
72. [X] **Задача (Implicit)**: Создать `TransactionController` с методами `index` и `show`.
73. [X] **Задача (Implicit)**: Создать Blade-шаблоны `admin/transactions/index.blade.php` и `admin/transactions/show.blade.php`.

## Этап 8: UI и Контроллеры - Роль Buyer

74. [ ] **Задача 8.1**: Адаптировать `DailyExpenseController` для баера (методы `index`, `create`, `store`, `edit`, `update` должны учитывать только его записи).
75. [ ] **Задача 8.2**: Адаптировать Blade-шаблоны `daily_expenses` для баера (скрыть выбор баера, возможно упростить).
76. [ ] **Задача 8.3**: Реализовать метод `buyerDashboard()` в `DashboardController`.
77. [ ] **Задача 8.4**: Создать Blade-шаблон для панели управления Баера (`dashboards/buyer.blade.php`).
78. [ ] **Задача 8.5**: Реализовать метод в `AccountController` для отображения информации о счете текущего баера.
79. [ ] **Задача 8.6**: Создать Blade-шаблон для просмотра счета баера (`accounts/my_account.blade.php`).
80. [ ] **Задача 8.7**: Настроить маршруты для Buyer (доступ к своим расходам, своему счету, своим отчетам).

## Этап 9: Интеграция с Keitaro

81. [ ] **Задача 9.1**: Создать Artisan команду `IncomeFetchKeitaro` (`php artisan make:command IncomeFetchKeitaro`).
82. [ ] **Задача 9.2**: Реализовать логику команды: использование `Http` клиента Laravel для запроса к API Keitaro (URL и API ключ из `.env`).
83. [ ] **Задача 9.3**: Реализовать сохранение полученных данных в таблицу `income_records`.
84. [ ] **Задача 9.4**: Зарегистрировать команду и настроить ее ежедневный запуск в `app/Console/Kernel.php`.
85. [ ] **Задача 9.5**: Создать `IncomeController` (`php artisan make:controller IncomeController`). Реализовать метод `index` для отображения записей о доходах.
86. [ ] **Задача 9.6**: Создать Blade-шаблон для отображения доходов (`income/index.blade.php`).
87. [ ] **Задача 9.7**: Добавить маршрут для просмотра доходов (доступен Owner/Finance?).

## Этап 10: Отчетность

88. [ ] **Задача 10.1**: Создать `ReportController` (`php artisan make:controller ReportController`).
89. [ ] **Задача 10.2**: Реализовать метод для отчета "Баланс по счетам" (агрегация `transaction_lines` по `account_id`).
90. [ ] **Задача 10.3**: Реализовать метод для отчета "Расходы баеров" (фильтрация `daily_expenses` и `adjustments` по баерам/командам/периодам).
91. [ ] **Задача 10.4**: Реализовать метод для отчета "Движение средств" (список `transactions` и `transaction_lines` с фильтрами).
92. [X] **Задача 10.5**: Создать Blade-шаблоны для отображения отчетов с фильтрами.
93. [ ] **Задача 10.6**: Настроить маршруты для отчетов с учетом прав доступа ролей.

## Этап 11: Валидация и Завершение

94. [X] **Задача 11.1**: Создать Form Requests (`php artisan make:request StoreTeamRequest`, `StoreUserRequest`, `StoreDailyExpenseRequest` и т.д.) для всех форм создания/редактирования. *(Partially Done: Created StoreFundTransferRequest)*
95. [X] **Задача 11.2**: Добавить правила валидации во все Form Requests. *(Partially Done: Added rules to StoreFundTransferRequest)*
96. [ ] **Задача 11.3**: Реализовать корректный расчет и отображение балансов счетов во всех необходимых местах.
97. [ ] **Задача 11.4**: Провести рефакторинг кода, улучшить UI/UX.
98. [ ] **Задача 11.5**: Написать тесты (Unit/Feature) для ключевой логики (генерация транзакций, авторизация, расчет балансов).
99. [ ] **Задача 11.6**: Провести полное ручное тестирование всех пользовательских сценариев для каждой роли.
100. [ ] **Задача 11.7**: Подготовить документацию по развертыванию (на основе `tech.md`). 
101. [ ] **Задача 11.8**: Определить четкую логику выбора системных счетов (SYSTEM_COMPANY, SYSTEM_OPERATIONS) для разных типов операций (расходы, корректировки, доходы?) в наблюдателях/сервисах. 