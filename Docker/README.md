# Student Attendance App

A simple web app to track student attendance. Built with Node.js, Express, and SQLite. Containerized with Docker.



## What it does

- Add students to the system
- Mark daily attendance (present or absent)
- View and filter attendance records
- See a summary report per student
- All data is saved in a local SQLite database


## Requirements

- [Docker](https://www.docker.com/products/docker-desktop) installed on your machine


## How to run it

**1. Clone or download this project**

**2. If this is your first time running Docker commands without `sudo`, run this once:**
```bash
sudo usermod -aG docker $USER
newgrp docker
```

**3. Start the app:**
```bash
docker-compose up
```

**4. Open your browser and go to:**
```
http://localhost:5000/index.html
```

**5. To stop the app:**
```bash
docker-compose down
```

Your data is saved in `attendance.db` on your computer. It will still be there when you start the app again.

---

## The two Docker files

### `Dockerfile`
This file is the **recipe for building the app image**. Think of it as instructions for Docker to set up the environment your app needs to run.

```dockerfile
FROM node:18-alpine          # Start from a lightweight Node.js base
WORKDIR /app                 # Set the working folder inside the container
COPY package*.json ./        # Copy dependency list first
RUN npm install --production # Install only what the app needs to run
COPY . .                     # Copy the rest of the project files
EXPOSE 5000                  # Tell Docker the app uses port 5000
ENV NODE_ENV=production      # Set environment to production
CMD ["npm", "start"]         # Start the app when the container runs
```

The reason we copy `package.json` and install dependencies *before* copying the full code is for caching  Docker won't re-run `npm install` every time you change your code, only when dependencies change.

---

### `docker-compose.yml`
This file is the **instructions for running the container**. It handles ports, volumes, and restart behaviour so you don't have to type long `docker run` commands manually.

```yaml
services:
  node-app:
    build: .                                        # Build image from the Dockerfile above
    container_name: student-attendance-app          # Give the container a readable name
    ports:
      - "5000:5000"                                 # Your computer:5000 → container:5000
    volumes:
      - ./attendance.db:/app/attendance.db          # Keep the database on your machine
    environment:
      NODE_ENV: production                          # Run in production mode
    restart: unless-stopped                         # Auto-restart if it crashes
    command: npm start                              # Start the app
```

The `volumes` line is the most important part,  it links the database file on your computer to the one inside the container. This means when you stop or delete the container, **your data is not lost**.

---

## Running Docker — screenshots

### 1. Fixing Docker permissions and starting `docker-compose up`

Before `docker-compose` could run, the user needed permission to access the Docker socket. The fix was adding the user to the `docker` group with `sudo usermod -aG docker $USER`, then applying it immediately with `newgrp docker`. After that, `docker-compose up` started building the image successfully.

![Fixing Docker permissions and running docker-compose up](images/Screenshot%20From%202026-06-11%2009-29-10.png)

---

### 2. `docker build` completing successfully

Running `docker build -t student-attendance-app .` went through all 5 build steps defined in the Dockerfile — pulling the base image, setting the working directory, copying files, installing dependencies, and copying the app code. The image was built and named `student-attendance-app:latest`.

![docker build finished successfully](images/Screenshot%20From%202026-06-11%2009-29-26.png)

---

### 3. `docker-compose up` — image built, container created

Running `docker-compose up` rebuilt the image, created the network, and created the container `student-attendance-app`. The build completed all 3/3 steps. The error at the bottom (`address already in use`) simply means port 5000 was already being used by the app running directly — stopping `npm start` first fixes this.

![docker-compose up building and running](images/Screenshot%20From%202026-06-11%2009-31-33.png)

---

### 4. Docker Desktop — image visible in the Images tab

After building, the `student-attendance-a` image (248.41 MB) appeared in Docker Desktop under the Images section, confirming the image was successfully built and stored locally.

![Docker Desktop showing the built image](images/Screenshot%20From%202026-06-11%2009-32-47.png)

---

## Project structure

```
Attendance/
├── src/
│   ├── app.js              # Express server
│   ├── db.js               # SQLite database setup
│   ├── routes/
│   │   ├── students.js     # Student API endpoints
│   │   └── attendance.js   # Attendance API endpoints
│   └── public/
│       ├── index.html      # Frontend UI
│       ├── style.css       # Styling
│       └── app.js          # Frontend logic
├── Dockerfile              # How to build the Docker image
├── docker-compose.yml      # How to run the container
├── package.json
└── attendance.db           # SQLite database (auto-created on first run)
```

---

## Useful commands

| What you want to do | Command |
|---|---|
| Start the app | `docker-compose up` |
| Start in background | `docker-compose up -d` |
| Stop the app | `docker-compose down` |
| View logs | `docker logs student-attendance-app` |
| Rebuild after code changes | `docker-compose up --build` |

---

## Note

The application code was written with the assistance of AI. However, I understand how Docker works — including how images are built from a `Dockerfile`, how `docker-compose` manages containers, how port mapping exposes services to the host machine, and how volumes are used to persist data outside the container lifecycle.
