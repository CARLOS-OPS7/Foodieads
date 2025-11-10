from sqlalchemy import Column, Integer, String, ForeignKey, Enum, DateTime, func
from ..database import Base
import enum


class PaymentMethod(str, enum.Enum):
    mpesa = "mpesa"
    card = "card"
    paypal = "paypal"


class PaymentStatus(str, enum.Enum):
    pending = "pending"
    confirmed = "confirmed"
    failed = "failed"


class Payment(Base):
    __tablename__ = "payments_api"
    id = Column(Integer, primary_key=True)
    restaurant_id = Column(Integer, ForeignKey("restaurants_api.id"), nullable=False)
    amount = Column(Integer, nullable=False)
    method = Column(Enum(PaymentMethod), nullable=False)
    status = Column(Enum(PaymentStatus), default=PaymentStatus.pending, nullable=False)
    transaction_ref = Column(String(128), nullable=True)
    created_at = Column(DateTime(timezone=True), server_default=func.now())




