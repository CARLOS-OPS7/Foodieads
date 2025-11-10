# FoodieAds Backend (FastAPI)

## Quick Start

1. Create venv and install deps:
```
python -m venv .venv
. .venv/Scripts/activate
pip install -r requirements.txt
```

2. Run the API:
```
uvicorn app.main:app --reload --port 8080
```

3. Env vars (optional):
- FOODIEADS_DATABASE_URL (default sqlite:///./foodieads.db)
- FOODIEADS_JWT_SECRET (default dev secret)

Docs: http://localhost:8000/foodieads-docs

Apache reverse-proxy (optional)
--------------------------------

If you host the PHP portal under Apache (e.g. XAMPP on port 8080) and run the FastAPI backend
via uvicorn on a different port (for example 8000), the portal's "/docs" and "/redoc" links
can be proxied through Apache so they open on the same origin. You can use the included
`apache-reverse-proxy-snippet.conf` as a starting point.

Steps:

1. Open `C:\xampp\apache\conf\httpd.conf` and ensure the following modules are enabled (uncomment if necessary):

	LoadModule proxy_module modules/mod_proxy.so
	LoadModule proxy_http_module modules/mod_proxy_http.so

2. Add the proxy snippet to your Apache configuration. You can place it in `httpd.conf` or
	`conf/extra/httpd-vhosts.conf`. The repository includes `apache-reverse-proxy-snippet.conf`.

3. Restart Apache (use the XAMPP Control Panel). Start uvicorn for the backend, for example:

	uvicorn backend.app.main:app --reload --port 8000

4. Open the portal at http://localhost:8080/api_portal.php and press the Docs / ReDoc buttons —
	they should open the proxied routes on the same host and port.

If you prefer not to proxy, you can change the portal base URL to point directly at the backend host:port
instead (for example http://localhost:8000), which is what the portal's input field lets you do.




