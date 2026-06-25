# Kubernetes Lab 1: PHP + Apache + MySQL + phpMyAdmin

This repository contains the code and configuration for running the Student Portal application. The project has been fully containerized and deployed to run on a local Kubernetes cluster.

## Architecture

- **Frontend**: A custom PHP/Apache web application.
- **Database**: MySQL 8.0 utilizing a Persistent Volume Claim (PVC) to ensure data isn't lost if the database pod restarts.
- **Management**: phpMyAdmin for graphical database administration.

## Prerequisites

Before starting, make sure you have the following tools installed and running:
- Docker
- `kubectl`
- A local Kubernetes cluster (like `kind` or Minikube)

## How to Run the Project

Follow these steps to spin up the application in your local Kubernetes cluster.

### 1. Build the Application Image
First, package the PHP application into a Docker image using the provided Dockerfile:
```bash
docker build -t student-app:latest .
```

### 2. Load the Image into Kubernetes
If you are using `kind`, you need to load your newly built image directly into the cluster so Kubernetes has access to it:
```bash
kind load docker-image student-app:latest
```

### 3. Deploy the Infrastructure
Apply all the Kubernetes configuration files located in the `kubernetes/` folder (this sets up your Deployments, Services, ConfigMaps, Secrets, and PVCs):
```bash
kubectl apply -f kubernetes/
```

### 4. Verify the Deployment
Check that all your pods have successfully started:
```bash
kubectl get pods
```
*Note: The MySQL pod might take a few minutes to pull the database image and fully initialize.*

## Accessing the Application

Because the cluster is running locally isolated inside Docker (via `kind`), the service ports are not automatically exposed to your host machine's browser. You need to use Kubernetes port forwarding to access the web interfaces.

Open two new terminal windows and run these commands to bridge the connections:

**To access the Student Portal:**
```bash
kubectl port-forward svc/app-service 30080:80
```
Then open your browser and navigate to: [http://localhost:30080](http://localhost:30080)

**To access phpMyAdmin:**
```bash
kubectl port-forward svc/phpmyadmin-service 30081:80
```
Then open your browser and navigate to: [http://localhost:30081](http://localhost:30081)
*(Login with username: `vscode` and password: `23264008`)*

## Testing Scaling and Self-Healing

To test Kubernetes scaling capabilities, you can increase the number of running PHP application replicas:
```bash
kubectl scale deployment student-app --replicas=3
```
Run `kubectl get pods` again to see the new instances running.

To test self-healing, try deleting one of the running pods:
```bash
kubectl delete pod <pod-name>
```
You will notice Kubernetes instantly detects the missing pod and spins up a new one to replace it automatically.

---
*Note: AI was only used to write code and readme files, all the concepts about kubernetes were understood.*
