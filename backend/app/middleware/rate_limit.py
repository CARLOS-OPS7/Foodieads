import time
from typing import Callable

from fastapi import Request, HTTPException


class RateLimitMiddleware:
    """Very small in-memory rate limiter for development use only.

    - Limits requests per IP per endpoint (path) to a small window.
    - Not suitable for multi-process or production; use Redis or reverse-proxy for production.
    """

    def __init__(self, app=None):
        self.app = app
        # shape: {(ip, path): [timestamps...]}
        self.storage = {}
        self.limit = 10  # requests
        self.window = 60  # seconds

    async def __call__(self, scope, receive, send):
        if scope["type"] != "http":
            await self.app(scope, receive, send)
            return

        request = Request(scope, receive=receive)
        client = request.client.host if request.client else "unknown"
        path = scope.get("path", "")

        # For non-sensitive paths, skip heavy checks
        if path.startswith("/api/auth"):
            # stricter limit for auth endpoints
            limit = 5
            window = 60
        elif path.startswith("/api/restaurants/upload"):
            limit = 5
            window = 60
        else:
            limit = self.limit
            window = self.window

        key = (client, path)
        now = time.time()
        hits = [t for t in self.storage.get(key, []) if now - t < window]
        hits.append(now)
        self.storage[key] = hits
        if len(hits) > limit:
            raise HTTPException(status_code=429, detail="Too many requests")

        await self.app(scope, receive, send)
