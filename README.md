# RecipeHub

RecipeHub — веб-приложение на Symfony для регистрации пользователей, создания рецептов и отзывов. Проект упакован в контейнеры через Docker Compose. Для базы данных используется PostgreSQL, для централизованного хранения логов — Loki, для просмотра логов — Grafana.

## Контейнеры

```text
app       - Symfony-приложение на FrankenPHP
postgres  - база данных PostgreSQL
loki      - централизованное хранилище логов
promtail  - сборщик файловых логов приложения
grafana   - просмотр логов из Loki
```

Схема сбора логов:

```text
Symfony Monolog -> /var/log/recipehub/*.log -> Promtail -> Loki -> Grafana
```

Собираются именно **логи приложения**, не метрики.

## Запуск

Собрать образ с тегом:

```bash
./scripts/build.sh -t v1
```

Запустить контейнеры с этим тегом:

```bash
./scripts/deploy.sh -t v1
```

Остановить контейнеры:

```bash
./scripts/down.sh
```

## Адреса

```text
Приложение: http://localhost:8080
Grafana:    http://localhost:3000
```

Данные для входа в Grafana:

```text
login:    admin
password: admin
```

## Проверка логов

1. Открыть приложение: `http://localhost:8080`.
2. Зарегистрировать пользователя или войти в аккаунт.
3. Создать рецепт или отзыв.
4. Открыть Grafana: `http://localhost:3000`.
5. Перейти в **Explore**.
6. Выбрать datasource **Loki**.
7. Выполнить запрос:

```logql
{app="recipehub"}
```

Примеры логируемых событий:

```text
User registered successfully
Login attempt
Login successful
Recipe created
Review created
Profanity detected
```

## Скриншоты

### Рабочее приложение

![Рабочее приложение](docs/screenshots/app-home.png)

### Запущенные контейнеры Docker Compose

![Docker Compose PS](docs/screenshots/docker-compose-ps.png)

### Grafana

![Grafana](docs/screenshots/grafana-home.png)

### Loki datasource в Grafana

![Loki datasource](docs/screenshots/grafana-loki-source.png)

### Логи приложения в Grafana Explore

![Логи в Grafana](docs/screenshots/grafana-logs.png)

### Детали записи лога с labels

![Детали лога](docs/screenshots/grafana-log-details.png)

## Полезные команды

Посмотреть контейнеры:

```bash
docker compose ps
```

Посмотреть логи приложения:

```bash
docker compose logs app
```

Проверить, что Promtail читает файлы логов:

```bash
docker compose logs promtail
```

Проверить labels в Loki:

```bash
docker compose exec loki wget -qO- 'http://localhost:3100/loki/api/v1/labels'
```
