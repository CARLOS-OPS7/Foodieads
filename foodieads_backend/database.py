from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker, declarative_base

# Simple SQLite setup for local development
# Use the existing DB in the workspace if present to avoid duplicating data.
# Absolute path on Windows: use forward slashes after the triple-slash.
DATABASE_URL = r"sqlite:///C:/xampp/htdocs/Auth/backend/foodieads.db"

# Create engine with check_same_thread disabled for SQLite in threaded servers.
engine = create_engine(DATABASE_URL, connect_args={"check_same_thread": False})
SessionLocal = sessionmaker(autocommit=False, autoflush=False, bind=engine)
Base = declarative_base()

def get_db():
    """Yield a database session for FastAPI dependencies."""
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()
