from fastapi import FastAPI, Form, File, UploadFile
from fastapi.middleware.cors import CORSMiddleware
from fastapi.staticfiles import StaticFiles
import os
import shutil

app = FastAPI()

# Allow all frontend origins (important for your HTML forms)
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_methods=["*"],
    allow_headers=["*"],
)

# Create uploads directory if missing
UPLOAD_DIR = "uploads"
os.makedirs(UPLOAD_DIR, exist_ok=True)

# Serve uploaded files (images)
app.mount("/uploads", StaticFiles(directory=UPLOAD_DIR), name="uploads")


@app.post("/api/restaurants")
async def add_restaurant(
    name: str = Form(...),
    location: str = Form(...),
    image: UploadFile = File(...)
):
    # Save uploaded image
    file_path = os.path.join(UPLOAD_DIR, image.filename)
    with open(file_path, "wb") as buffer:
        shutil.copyfileobj(image.file, buffer)

    # Build the public image URL
    image_url = f"http://127.0.0.1:8000/uploads/{image.filename}"

    # Return restaurant details
    return {
        "name": name,
        "location": location,
        "image_url": image_url,
        "message": "Restaurant added successfully!"
    }
