from fastapi import FastAPI
import os
from fastapi.middleware.cors import CORSMiddleware
from fastapi.staticfiles import StaticFiles
from .middleware.rate_limit import RateLimitMiddleware

from .routes import auth, restaurants, ads, payments
from .database import init_database

app = FastAPI(title="FoodieAds API", version="0.1.0")

# Mount uploads directory so uploaded files are served at /uploads/
uploads_path = os.path.abspath(os.path.join(os.path.dirname(__file__), "..", "..", "uploads"))
os.makedirs(uploads_path, exist_ok=True)
app.mount("/uploads", StaticFiles(directory=uploads_path), name="uploads")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Simple in-memory rate limiter middleware (applies to all routes; it internally checks path)
app.add_middleware(RateLimitMiddleware)


@app.on_event("startup")
def on_startup():
    init_database()


app.include_router(auth.router, prefix="/api/auth", tags=["auth"])
app.include_router(restaurants.router, prefix="/api/restaurants", tags=["restaurants"])
app.include_router(ads.router, prefix="/api/ads", tags=["ads"])
app.include_router(payments.router, prefix="/api/payments", tags=["payments"])


@app.get("/api/health")
def health():
    return {"ok": True}




