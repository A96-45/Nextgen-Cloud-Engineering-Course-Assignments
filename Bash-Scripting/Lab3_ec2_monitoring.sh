#!/bin/bash

# Lab 3 - AWS EC2 Monitoring Automation
# Monitors CPU, Memory, Disk usage and stores logs

MONITOR_LOG="/var/log/monitoring/system_monitor.log"
ALERT_THRESHOLD_CPU=80
ALERT_THRESHOLD_MEM=80
ALERT_THRESHOLD_DISK=85

# Create monitoring directory
mkdir -p /var/log/monitoring

log_metrics() {
    local timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    echo "$timestamp - CPU: ${cpu_usage}% | Memory: ${mem_usage}% | Disk: ${disk_usage}%" >> $MONITOR_LOG
}

get_cpu_usage() {
    cpu_usage=$(top -bn1 | grep "Cpu(s)" | awk '{print int($2)}')
    echo $cpu_usage
}

get_memory_usage() {
    mem_usage=$(free | grep Mem | awk '{print int($3/$2 * 100)}')
    echo $mem_usage
}

get_disk_usage() {
    disk_usage=$(df / | awk 'NR==2 {print int($5)}')
    echo $disk_usage
}

check_cpu() {
    local cpu=$(get_cpu_usage)
    echo "CPU Usage: ${cpu}%"
    
    if [ $cpu -gt $ALERT_THRESHOLD_CPU ]; then
        echo "WARNING: CPU usage above threshold ($cpu% > $ALERT_THRESHOLD_CPU%)"
        echo "[ALERT] CPU high at $cpu% - $(date)" >> $MONITOR_LOG
    fi
    
    return $cpu
}

check_memory() {
    local mem=$(get_memory_usage)
    echo "Memory Usage: ${mem}%"
    
    if [ $mem -gt $ALERT_THRESHOLD_MEM ]; then
        echo "WARNING: Memory usage above threshold ($mem% > $ALERT_THRESHOLD_MEM%)"
        echo "[ALERT] Memory high at $mem% - $(date)" >> $MONITOR_LOG
    fi
    
    return $mem
}

check_disk() {
    local disk=$(get_disk_usage)
    echo "Disk Usage: ${disk}%"
    
    if [ $disk -gt $ALERT_THRESHOLD_DISK ]; then
        echo "WARNING: Disk usage above threshold ($disk% > $ALERT_THRESHOLD_DISK%)"
        echo "[ALERT] Disk usage high at $disk% - $(date)" >> $MONITOR_LOG
    fi
    
    return $disk
}

generate_report() {
    echo ""
    echo "=== System Monitoring Report ==="
    echo "Generated: $(date)"
    echo "Hostname: $(hostname)"
    echo ""
    
    check_cpu
    check_memory
    check_disk
    
    echo ""
    echo "Thresholds: CPU=$ALERT_THRESHOLD_CPU% | MEM=$ALERT_THRESHOLD_MEM% | DISK=$ALERT_THRESHOLD_DISK%"
    echo "Log file: $MONITOR_LOG"
}

main() {
    echo "Starting EC2 Monitoring..."
    
    cpu_usage=$(get_cpu_usage)
    mem_usage=$(get_memory_usage)
    disk_usage=$(get_disk_usage)
    
    log_metrics
    generate_report
    
    echo ""
    echo "Monitoring complete. Logs stored in $MONITOR_LOG"
}

main

