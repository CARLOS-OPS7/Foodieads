from fastapi import APIRouter, Depends, HTTPException
from sqlalchemy.orm import Session
from .. import models, database
from pydantic import BaseModel

router = APIRouter()

class RestaurantCreate(BaseModel):
    name: str
    address: str | None = None


@router.post("/", response_model=dict)
def create_restaurant(r: RestaurantCreate, db: Session = Depends(database.get_db)):
    db_r = models.Restaurant(name=r.name, address=r.address)
    db.add(db_r)
    db.commit()
    db.refresh(db_r)
    return {"id": db_r.id, "name": db_r.name}


@router.get("/{rest_id}")
def get_restaurant(rest_id: int, db: Session = Depends(database.get_db)):
    rest = db.query(models.Restaurant).filter(models.Restaurant.id == rest_id).first()
    if not rest:
        raise HTTPException(status_code=404, detail="Restaurant not found")
    return {"id": rest.id, "name": rest.name, "address": rest.address}
