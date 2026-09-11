from aiogram import Router, F
from aiogram.types import Message
from config import SUPERGROUP_ID, TOPICS
from services.ai_service import ai_service
from services.mcp_service import get_masters_schedules_from_calendar

router = Router()

@router.message(F.chat.id == SUPERGROUP_ID, F.message_thread_id == TOPICS["SCHEDULES"])
async def handle_schedule_conversation(message: Message):
    # 
    if message.from_user.is_bot:
        return
        
    # 1. 
    calendar_data = await get_masters_schedules_from_calendar()
    
    # 2. 
    reply_text = await ai_service.negotiate_schedule(
        master_message=message.text, 
        calendar_slots=calendar_data
    )
    
    # 3. 
    await message.reply(reply_text)
