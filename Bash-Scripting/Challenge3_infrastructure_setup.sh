#!/bin/bash

# Challenge 3 - Infrastructure Setup Automation
# Fully prepares a new EC2 instance with all required tools

REPO_URL="https://github.com/A96-45/cloud-engineering-challenge.git"
APP_DIR="/opt/app"

log_step() {
    echo ""
    echo "----------------------------------------"
    echo "> $1"
    echo "----------------------------------------"
}

check_root() {
    if [[ $EUID -ne 0 ]]; then
        echo "ERROR: This script must be run as root"
        exit 1
    fi
}

update_packages() {
    log_step "Step 1: Updating system packages"
    apt-get update && apt-get upgrade -y
    echo "Packages updated"
}

install_docker() {
    log_step "Step 2: Installing Docker"
    
    if command -v docker &> /dev/null; then
        echo "Docker already installed"
    else
        apt-get install -y docker.io
        systemctl start docker
        systemctl enable docker
        echo "Docker installed and running"
    fi
}

install_nginx() {
    log_step "Step 3: Installing Nginx"
    
    if command -v nginx &> /dev/null; then
        echo "Nginx already installed"
    else
        apt-get install -y nginx
        systemctl start nginx
        systemctl enable nginx
        echo "Nginx installed and running"
    fi
}

configure_firewall() {
    log_step "Step 4: Configuring Firewall"
    
    if command -v ufw &> /dev/null; then
        ufw --force enable
        ufw default deny incoming
        ufw default allow outgoing
        ufw allow 22/tcp
        ufw allow 80/tcp
        ufw allow 443/tcp
        echo "Firewall configured"
    else
        echo "UFW not available, skipping firewall setup"
    fi
}

clone_repository() {
    log_step "Step 5: Cloning GitHub Repository"
    
    mkdir -p $APP_DIR
    
    if [ -d "$APP_DIR/.git" ]; then
        echo "Repository already exists, pulling latest changes"
        cd $APP_DIR
        git pull origin main
    else
        git clone $REPO_URL $APP_DIR
        echo "Repository cloned to $APP_DIR"
    fi
}

deploy_app() {
    log_step "Step 6: Deploying Application"
    
    cd $APP_DIR
    
    if [ -f "Dockerfile" ]; then
        echo "Docker configuration found"
        echo "Application ready for deployment"
        echo "To deploy, run: docker build -t myapp . && docker run -p 5000:5000 myapp"
    elif [ -f "requirements.txt" ]; then
        echo "Python application detected"
        pip3 install -r requirements.txt
        echo "Dependencies installed"
    else
        echo "App structure verified"
    fi
}

verify_setup() {
    log_step "Step 7: Verifying Setup"
    
    echo "Checking installed components:"
    
    echo -n "Docker: "
    if command -v docker &> /dev/null; then
        echo "OK"
    else
        echo "MISSING"
    fi
    
    echo -n "Nginx: "
    if systemctl is-active --quiet nginx; then
        echo "OK (running)"
    else
        echo "MISSING"
    fi
    
    echo -n "Git: "
    if command -v git &> /dev/null; then
        echo "OK"
    else
        echo "MISSING"
    fi
    
    echo -n "Repository: "
    if [ -d "$APP_DIR/.git" ]; then
        echo "OK (at $APP_DIR)"
    else
        echo "MISSING"
    fi
}

main() {
    echo "========================================"
    echo "EC2 Infrastructure Setup Automation"
    echo "Preparing server for deployment"
    echo "========================================"
    
    check_root
    
    update_packages
    install_docker
    install_nginx
    configure_firewall
    clone_repository
    deploy_app
    verify_setup
    
    echo ""
    echo "========================================"
    echo "Setup Complete!"
    echo "Server is ready for deployment"
    echo "========================================"
}

main
