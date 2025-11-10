import os
import time
import jwt

JWT_SECRET = os.getenv("FOODIEADS_JWT_SECRET", "dev_secret_change_me")
JWT_ISSUER = "foodieads"
JWT_EXP_SECONDS = 60 * 60 * 24


def create_token(user_id: int, role: str) -> str:
    now = int(time.time())
    payload = {
        "sub": str(user_id),
        "role": role,
        "iss": JWT_ISSUER,
        "iat": now,
        "exp": now + JWT_EXP_SECONDS,
    }
    return jwt.encode(payload, JWT_SECRET, algorithm="HS256")


def verify_token(token: str) -> dict:
    return jwt.decode(token, JWT_SECRET, algorithms=["HS256"], issuer=JWT_ISSUER)




