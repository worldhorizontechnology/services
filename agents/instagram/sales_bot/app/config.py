import os
from pathlib import Path

from dotenv import load_dotenv

PROJECT_DIR = Path(__file__).resolve().parents[1]
try:
    load_dotenv(PROJECT_DIR / ".env")
except Exception as error:
    raise RuntimeError(f"Could not load agent .env: {error}") from error

MCP_SERVER_URL = os.getenv("MCP_SERVER_URL", "http://127.0.0.1:8788/")
GEMINI_API_KEY = os.getenv("GEMINI_API_KEY", "")
META_VERIFY_TOKEN = os.getenv("META_VERIFY_TOKEN", "")
META_PAGE_ACCESS_TOKEN = os.getenv("META_PAGE_ACCESS_TOKEN", "")


def require(value: str, name: str) -> str:
    try:
        if not value:
            raise RuntimeError(f"Missing required Instagram agent setting: {name}")
        return value
    except Exception as error:
        raise RuntimeError(f"Invalid Instagram agent setting {name}: {error}") from error
