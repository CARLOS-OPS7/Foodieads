from sqlalchemy import Column, Integer, String, ForeignKey, Text
from sqlalchemy.orm import relationship
from ..database import Base


class Restaurant(Base):
    __tablename__ = "restaurants_api"
    id = Column(Integer, primary_key=True)
    user_id = Column(Integer, ForeignKey("users_api.id"), nullable=False)
    name = Column(String(160), nullable=False)
    location = Column(String(160), nullable=True)
    description = Column(Text, nullable=True)
    # optional image paths (nullable for backward compatibility)
    image_url = Column(String(255), nullable=True)
    thumb_url = Column(String(255), nullable=True)

    owner = relationship("User")




