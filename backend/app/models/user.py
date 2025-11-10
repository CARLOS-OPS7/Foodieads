from sqlalchemy import Column, Integer, String, Enum, DateTime, func
from ..database import Base
import enum


class RoleEnum(str, enum.Enum):
    admin = "admin"
    owner = "owner"
    customer = "customer"


class User(Base):
    __tablename__ = "users_api"
    id = Column(Integer, primary_key=True, index=True)
    name = Column(String(120), nullable=False)
    email = Column(String(160), unique=True, index=True, nullable=False)
    password_hash = Column(String(255), nullable=False)
    role = Column(Enum(RoleEnum), default=RoleEnum.owner, nullable=False)
    created_at = Column(DateTime(timezone=True), server_default=func.now())




