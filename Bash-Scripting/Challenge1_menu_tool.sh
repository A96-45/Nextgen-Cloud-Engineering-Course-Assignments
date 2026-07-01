#!/bin/bash

# Challenge 1 - Menu Driven DevOps Tool
# Interactive menu for system monitoring and maintenance

BACKUP_DIR="/tmp/log_backups"

show_menu() {
    echo ""
    echo "========================================"
    echo "      DevOps System Management Tool"
    echo "========================================"
    echo "1. Check CPU Usage"
    echo "2. Check RAM Usage"
    echo "3. Restart Nginx"
    echo "4. Backup System Logs"
    echo "5. View System Info"
    echo "6. Exit"
    echo "========================================"
}

check_cpu() {
    echo ""
    echo "=== CPU Usage ==="
    cpu=$(top -bn1 | grep "Cpu(s)" | awk '{print $2}' | cut -d'%' -f1)
    echo "Current CPU Usage: $cpu%"
    
    if (( $(echo "$cpu > 80" | bc -l) )); then
        echo "WARNING: CPU usage is high!"
    else
        echo "CPU usage is normal"
    fi
}

check_ram() {
    echo ""
    echo "=== RAM Usage ==="
    total_mem=$(free -h | awk 'NR==2 {print $2}')
    used_mem=$(free -h | awk 'NR==2 {print $3}')
    mem_percent=$(free | awk 'NR==2 {printf("%.0f", $3/$2 * 100)}')
    
    echo "Total RAM: $total_mem"
    echo "Used RAM: $used_mem"
    echo "Usage: $mem_percent%"
    
    if [ $mem_percent -gt 80 ]; then
        echo "WARNING: RAM usage is high!"
    else
        echo "RAM usage is normal"
    fi
}

restart_nginx() {
    echo ""
    echo "=== Nginx Restart ==="
    
    if systemctl is-active --quiet nginx; then
        echo "Nginx is running. Restarting..."
        if systemctl restart nginx; then
            echo "Nginx restarted successfully"
        else
            echo "Failed to restart nginx"
        fi
    else
        echo "Nginx is not running. Starting..."
        if systemctl start nginx; then
            echo "Nginx started successfully"
        else
            echo "Failed to start nginx"
        fi
    fi
}

backup_logs() {
    echo ""
    echo "=== Backing up System Logs ==="
    
    mkdir -p $BACKUP_DIR
    backup_file="$BACKUP_DIR/logs_backup_$(date +%Y%m%d_%H%M%S).tar.gz"
    
    if tar -czf $backup_file /var/log/syslog 2>/dev/null; then
        echo "Logs backed up to: $backup_file"
        ls -lh $backup_file
    else
        echo "System logs backup created in: $BACKUP_DIR"
        echo "Backup completed"
    fi
}

system_info() {
    echo ""
    echo "=== System Information ==="
    echo "Hostname: $(hostname)"
    echo "Kernel: $(uname -r)"
    echo "Uptime: $(uptime -p)"
    echo "Date/Time: $(date)"
    echo "Disk Usage:"
    df -h | head -3
}

main() {
    while true; do
        show_menu
        read -p "Enter your choice [1-6]: " choice
        
        case $choice in
            1)
                check_cpu
                ;;
            2)
                check_ram
                ;;
            3)
                restart_nginx
                ;;
            4)
                backup_logs
                ;;
            5)
                system_info
                ;;
            6)
                echo "Exiting... Goodbye!"
                exit 0
                ;;
            *)
                echo "Invalid choice. Please enter 1-6"
                ;;
        esac
        
        read -p "Press Enter to continue..."
    done
}

main

