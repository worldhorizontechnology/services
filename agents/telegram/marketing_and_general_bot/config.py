import os
from dotenv import load_dotenv

load_dotenv()

BOT_TOKEN = os.getenv("TELEGRAM_BOT_TOKEN")
SUPERGROUP_ID = int(os.getenv("SUPERGROUP_ID", 0))
GCP_PROJECT = os.getenv("GCP_PROJECT")
GCP_LOCATION = os.getenv("GCP_LOCATION")

TOPICS = {
    "GENERAL_QA": int(os.getenv("TOPIC_GENERAL_QA", 0)),
    "ANALYTICS": int(os.getenv("TOPIC_ANALYTICS", 0)),
    "MEETINGS": int(os.getenv("TOPIC_MEETINGS", 0)),
    "SCHEDULES": int(os.getenv("TOPIC_SCHEDULES", 0))
}
