from typing import List, Dict, Optional
import os
from uuid import uuid4

from fastapi import APIRouter, HTTPException, Form, File, UploadFile, Depends
from pydantic import BaseModel
from sqlalchemy.orm import Session

from ..database import get_db
from ..models.restaurant import Restaurant as RestaurantModel

router = APIRouter()


class RestaurantIn(BaseModel):
    name: str
    location: str


class RestaurantOut(RestaurantIn):
    id: int


# Simple in-memory store for demo / tests. In production this should use the DB layer.
# Note: previously this used an in-memory list. We'll persist to the DB.


@router.get("/", response_model=List[RestaurantOut])
def list_restaurants(db: Session = Depends(get_db)):
    rows = db.query(RestaurantModel).all()
    return [
        {"id": r.id, "name": r.name, "location": r.location, "image_url": getattr(r, "image_url", None)} for r in rows
    ]


@router.post("/", response_model=RestaurantOut)
def add_restaurant(restaurant: RestaurantIn, db: Session = Depends(get_db)):
    # default owner user_id to 1 (adjust when auth is integrated)
    db_obj = RestaurantModel(user_id=1, name=restaurant.name, location=restaurant.location)
    db.add(db_obj)
    db.commit()
    db.refresh(db_obj)
    return {"id": db_obj.id, "name": db_obj.name, "location": db_obj.location}


@router.delete("/{id}")
def delete_restaurant(id: int, db: Session = Depends(get_db)):
    r = db.query(RestaurantModel).filter(RestaurantModel.id == id).first()
    if not r:
        raise HTTPException(status_code=404, detail="Restaurant not found")
    db.delete(r)
    db.commit()
    return {"message": f"Restaurant {id} deleted"}


@router.post("/upload")
async def add_restaurant_with_image(name: str = Form(...), location: str = Form(...), image: UploadFile = File(...)):
    # configuration
    MAX_SIZE = 5 * 1024 * 1024  # 5MB max

    # ensure uploads dir exists (project-root/uploads)
    uploads_dir = os.path.join(os.path.dirname(os.path.dirname(os.path.dirname(__file__))), "uploads")
    os.makedirs(uploads_dir, exist_ok=True)

    # basic content-type check
    content_type = (image.content_type or "").lower()
    if not content_type.startswith("image/"):
        raise HTTPException(status_code=400, detail="Uploaded file must be an image")

    # read and enforce size limit
    contents = await image.read()
    if len(contents) > MAX_SIZE:
        raise HTTPException(status_code=413, detail="Image too large (max 5MB)")

    # generate safe filename
    orig_name = os.path.basename(image.filename or "upload")
    ext = os.path.splitext(orig_name)[1]
    if not ext:
        # try to infer from content type
        ext = {
            "image/jpeg": ".jpg",
            "image/png": ".png",
            "image/gif": ".gif",
            "image/webp": ".webp",
        }.get(content_type, "")

    safe_filename = f"{uuid4().hex}{ext}"
    save_path = os.path.join(uploads_dir, safe_filename)

    # write file
    with open(save_path, "wb") as f:
        f.write(contents)

    # optional thumbnail generation if Pillow is installed
    thumb_url: Optional[str] = None
    try:
        from PIL import Image

        img = Image.open(save_path)
        img.thumbnail((800, 800))
        thumb_name = f"thumb_{safe_filename}"
        thumb_path = os.path.join(uploads_dir, thumb_name)
        img.save(thumb_path, optimize=True, quality=85)
        thumb_url = f"/uploads/{thumb_name}"
    except Exception:
        # Pillow not installed or processing failed; ignore thumbnail
        thumb_url = None

    image_url = f"/uploads/{safe_filename}"

    # persist to DB
    db_obj = RestaurantModel(user_id=1, name=name, location=location, image_url=image_url, thumb_url=thumb_url)
    db = next(get_db())
    db.add(db_obj)
    db.commit()
    db.refresh(db_obj)
    return {"id": db_obj.id, "name": db_obj.name, "location": db_obj.location, "image_url": image_url, "thumb_url": thumb_url}




