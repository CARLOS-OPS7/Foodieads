from fastapi import APIRouter, Depends, HTTPException
from sqlalchemy.orm import Session
from .. import models, database
from pydantic import BaseModel

router = APIRouter()

class AdCreate(BaseModel):
    title: str
    description: str | None = None
    price: float | None = 0.0
    restaurant_id: int


@router.post("/", response_model=dict)
def create_ad(ad: AdCreate, db: Session = Depends(database.get_db)):
    db_ad = models.Ad(title=ad.title, description=ad.description, price=ad.price, restaurant_id=ad.restaurant_id)
    db.add(db_ad)
    db.commit()
    db.refresh(db_ad)
    return {"id": db_ad.id, "title": db_ad.title}


@router.get("/{ad_id}")
def get_ad(ad_id: int, db: Session = Depends(database.get_db)):
    ad = db.query(models.Ad).filter(models.Ad.id == ad_id).first()
    if not ad:
        raise HTTPException(status_code=404, detail="Ad not found")
    return {"id": ad.id, "title": ad.title, "description": ad.description, "price": ad.price}
