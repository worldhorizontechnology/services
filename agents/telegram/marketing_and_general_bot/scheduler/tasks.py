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
