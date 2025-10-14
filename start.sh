#!/bin/bash

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1"
}

log "=== Starting Laravel Queue and Scheduler ==="

log "Waiting for database..."
sleep 5

log "Starting Queue Worker..."
php artisan queue:work database --sleep=3 --tries=2 --timeout=1800 --verbose &
QUEUE_PID=$!

log "Starting Scheduler..."
(
    while true; do
        php artisan schedule:run --verbose --no-interaction
        sleep 60
    done
) &
SCHEDULER_PID=$!

log "Queue Worker PID: $QUEUE_PID"
log "Scheduler PID: $SCHEDULER_PID"
log "=== All services started successfully! ==="
log ""

# Мониторим процессы и автоматически перезапускаем при падении
while true; do
    # Проверяем что queue worker работает
    if ! kill -0 $QUEUE_PID 2>/dev/null; then
        log "ERROR: Queue Worker died! Restarting..."
        php artisan queue:work database --sleep=3 --tries=2 --timeout=1800 --verbose &
        QUEUE_PID=$!
        log "Queue Worker restarted with PID: $QUEUE_PID"
    fi

    # Проверяем что scheduler работает
    if ! kill -0 $SCHEDULER_PID 2>/dev/null; then
        log "ERROR: Scheduler died! Restarting..."
        (
            while true; do
                php artisan schedule:run --verbose --no-interaction
                sleep 60
            done
        ) &
        SCHEDULER_PID=$!
        log "Scheduler restarted with PID: $SCHEDULER_PID"
    fi

    sleep 10
done
