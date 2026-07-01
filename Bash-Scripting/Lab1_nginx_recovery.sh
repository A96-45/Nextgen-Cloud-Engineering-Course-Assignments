#!/bin/bash

# Lab 1 - Production Server Recovery Script
# Detects nginx crash, restarts it, logs issue, sends notification

LOG_FILE="/var/log/nginx_recovery.log"
ALERT_FILE="/tmp/nginx_alert.txt"
NGINX_SERVICE="nginx"

# Create log file if it doesn't exist
mkdir -p /var/log
touch $LOG_FILE

log_event() {
    local message="$1"
    local timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    echo "[$timestamp] $message" >> $LOG_FILE
    echo "[$timestamp] $message"
}

check_nginx() {
    if systemctl is-active --quiet $NGINX_SERVICE; then
        return 0
    else
        return 1
    fi
}

restart_nginx() {
    log_event "Nginx is down. Attempting restart..."
    
    if systemctl restart $NGINX_SERVICE; then
        log_event "Nginx restarted successfully"
        return 0
    else
        log_event "ERROR: Failed to restart nginx"
        return 1
    fi
}

send_notification() {
    local status="$1"
    local alert_msg="Nginx Status Alert - $status
Time: $(date)
Server: $(hostname)
Status: $(systemctl is-active $NGINX_SERVICE)"
    
    echo "$alert_msg" > $ALERT_FILE
    log_event "Notification sent: $status"
}

verify_health() {
    log_event "Verifying nginx health..."
    
    if systemctl is-active --quiet $NGINX_SERVICE; then
        if curl -s http://localhost >/dev/null 2>&1; then
            log_event "Nginx health check: PASSED"
            return 0
        else
            log_event "WARNING: Nginx running but not responding to requests"
            return 1
        fi
    else
        log_event "ERROR: Nginx is not running"
        return 1
    fi
}

main() {
    echo "=== Nginx Recovery Script Started ==="
    log_event "Script execution started"
    
    if check_nginx; then
        echo "Nginx is running. No action needed."
        log_event "Nginx status check: Running"
        send_notification "HEALTHY"
    else
        echo "Nginx is down! Starting recovery..."
        restart_nginx
        sleep 2
        verify_health
        
        if check_nginx; then
            send_notification "RECOVERED"
            echo "Recovery successful!"
        else
            send_notification "FAILED"
            echo "Recovery failed. Manual intervention needed."
            exit 1
        fi
    fi
    
    log_event "Script execution completed"
    echo "=== Script Completed ==="
}

main
