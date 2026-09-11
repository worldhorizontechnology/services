from aiogram import Bot
from config import SUPERGROUP_ID, TOPICS
from services.ai_service import ai_service
from services.mcp_service import get_laravel_snapshot, get_workspace_docs

async def task_generate_analytics(bot: Bot):
    # 1. 
    laravel_data = await get_laravel_snapshot()
    
    # 2. 
    rag_context = await ai_service.get_rag_context("Цены, услуги, правила бренда")
    
    # 3. Генерируем отчет
    report_text = await ai_service.generate_marketing_action(laravel_data, rag_context)
    
    # 4. Отправляем в Telegram
    await bot.send_message(
        chat_id=SUPERGROUP_ID,
        message_thread_id=TOPICS["ANALYTICS"],
        text=report_text
    )

async def task_check_and_request_schedules(bot: Bot):
    """Проактивная проверка: если графики не заполнили, тегаем в топике расписания."""
    calendar_data = await get_masters_schedules_from_calendar()
    missing_users = calendar_data.get("unfilled_masters", [])
    
    if missing_users:
        mentions = ", ".join([f"@{u['username']}" for u in missing_users])
        free_slots = calendar_data.get("free_slots_summary", "Пн-Пт 10:00-18:00")
        
        text = (
            f"⚠️ <b>Внимание, не заполнен график на следующую неделю!</b>\n"
            f"Напоминание для: {mentions}\n\n"
            f"💡 Свободные приоритетные слоты: {free_slots}.\n"
            f"Ответьте в этот топик, какое время вам удобно, я забронирую его в Google Календаре."
        )
        
        await bot.send_message(
            chat_id=SUPERGROUP_ID,
            message_thread_id=TOPICS["SCHEDULES"],
            text=text
        )
