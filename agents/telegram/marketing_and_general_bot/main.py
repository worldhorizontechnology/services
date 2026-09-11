import asyncio
from aiogram import Bot, Dispatcher
from apscheduler.schedulers.asyncio import AsyncIOScheduler

from config import BOT_TOKEN
from bot.routers.qa import router as qa_router
from bot.routers.meetings import router as meetings_router
from scheduler.tasks import task_generate_analytics
from services.mcp_service import mcp

async def main():
    bot = Bot(token=BOT_TOKEN)
    dp = Dispatcher()
    
    # 
    dp.include_router(qa_router)
    #dp.include_router(meetings_router)
    dp.include_router(schedules_router)
    
    # 
    await mcp.connect()
    
    # 
    scheduler = AsyncIOScheduler()
    scheduler.add_job(
        task_generate_analytics, 
        'cron', 
        hour=9, 
        kwargs={'bot': bot}
    )
    scheduler.start()
    
    # Запуск поллинга
    await dp.start_polling(bot)

if __name__ == "__main__":
    asyncio.run(main())
