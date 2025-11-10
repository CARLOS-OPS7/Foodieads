from sqlalchemy import Column, Integer, String, ForeignKey, Text, Enum
from ..database import Base
import enum


class AdStatus(str, enum.Enum):
    active = "active"
    paused = "paused"


class Ad(Base):
    __tablename__ = "ads_api"
    id = Column(Integer, primary_key=True)
    restaurant_id = Column(Integer, ForeignKey("restaurants_api.id"), nullable=False)
    title = Column(String(160), nullable=False)
    description = Column(Text, nullable=True)
    image_url = Column(String(255), nullable=True)
    status = Column(Enum(AdStatus), default=AdStatus.paused, nullable=False)




