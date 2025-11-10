FROM python:3.11-slim

# Create app directory
WORKDIR /app

# Install system deps (minimal)
RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential \
 && rm -rf /var/lib/apt/lists/*

# Copy requirements and install
COPY foodieads_backend/requirements.txt /app/requirements.txt
RUN pip install --no-cache-dir -r /app/requirements.txt

# Copy the package
COPY . /app

# Expose the port the app will run on
EXPOSE 8080

# Default command: run uvicorn on 0.0.0.0:8080 with reload (dev-friendly)
CMD ["uvicorn", "foodieads_backend.main:app", "--host", "0.0.0.0", "--port", "8080", "--reload"]
