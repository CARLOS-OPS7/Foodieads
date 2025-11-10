# FoodieAds Backend (local dev)

This small guide shows two ways to run the `foodieads_backend` FastAPI app on port 8080.

Option A — Run locally (requires Python installed)

1. Create & activate a virtual environment (PowerShell):

```powershell
cd C:\xampp\htdocs\Auth
python -m venv .venv
.\.venv\Scripts\Activate.ps1
pip install -r foodieads_backend\requirements.txt
```

2. Run the app on port 8080:

```powershell
python -m uvicorn foodieads_backend.main:app --reload --host 127.0.0.1 --port 8080
```

Option B — Run with Docker (no local Python required)

1. Build and start the container (from project root):

```powershell
# Build and start
docker compose up --build

# Or run directly with docker
docker build -t foodieads-backend .
docker run --rm -p 8080:8080 -v ${PWD}:/app foodieads-backend
```

2. Open the API in your browser or test with PowerShell:

```powershell
Invoke-RestMethod -Uri 'http://127.0.0.1:8080/'
```

Notes
- The Docker setup maps host port 8080 to the container's port 8080.
- The app is configured to use the existing SQLite DB at `C:/xampp/htdocs/Auth/backend/foodieads.db`.
- If you prefer a different port, change the `-p` mapping in `docker run` or `docker-compose.yml` and the CMD args in the `Dockerfile`.
