from fastapi import APIRouter, Depends, HTTPException
from sqlalchemy.orm import Session
from passlib.hash import bcrypt

from ..database import get_db
from ..models.user import User, RoleEnum
from ..schemas.user_schema import UserCreate, UserLogin, UserOut
from ..utils.jwt_helper import create_token

router = APIRouter()


@router.post("/signup", response_model=UserOut)
def signup(payload: UserCreate, db: Session = Depends(get_db)):
    if db.query(User).filter(User.email == payload.email).first():
        raise HTTPException(status_code=400, detail="Email already registered")
    user = User(
        name=payload.name,
        email=payload.email,
        password_hash=bcrypt.hash(payload.password),
        role=RoleEnum.owner,
    )
    db.add(user)
    db.commit()
    db.refresh(user)
    return user


@router.post("/login")
def login(payload: UserLogin, db: Session = Depends(get_db)):
    user = db.query(User).filter(User.email == payload.email).first()
    if not user or not bcrypt.verify(payload.password, user.password_hash):
        raise HTTPException(status_code=401, detail="Invalid credentials")
    token = create_token(user.id, user.role.value)
    return {"access_token": token, "token_type": "bearer"}




