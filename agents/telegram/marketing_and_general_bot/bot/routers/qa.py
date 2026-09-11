from aiogram import Router, F
from aiogram.types import Message
from services.ai_service import ai_service
from config import SUPERGROUP_ID

router = Router()

@router.message(F.chat.id == SUPERGROUP_ID, F.text.startswith("?"))
async def handle_question(message: Message):
    query = message.text.lstrip("?").strip()
    
    context = await ai_service.get_rag_context(query)
    prompt = f"Контекст: {context}\nВопрос: {query}"
    
    response_text = await ai_service.llm.generate_content_async(prompt)
    await message.reply(response_text.text)
