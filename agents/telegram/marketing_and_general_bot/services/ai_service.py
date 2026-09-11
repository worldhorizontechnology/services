import asyncio
import vertexai
from vertexai.generative_models import GenerativeModel, Tool, grounding
from langchain_google_vertexai import VertexAIEmbeddings
from langchain_community.vectorstores import FAISS
from langchain.text_splitter import RecursiveCharacterTextSplitter
from config import GCP_PROJECT, GCP_LOCATION

class AIService:
    def __init__(self):
        vertexai.init(project=GCP_PROJECT, location=GCP_LOCATION)
        self.llm = GenerativeModel("gemini-1.5-pro")
        self.embeddings = VertexAIEmbeddings(model_name="textembedding-gecko@003")
        self.vector_store = None 

    async def update_vector_store(self, raw_documents_text: str):
        text_splitter = RecursiveCharacterTextSplitter(chunk_size=1000, chunk_overlap=200)
        chunks = text_splitter.split_text(raw_documents_text)
        # В продакшене FAISS заменяется на pgvector или ChromaDB
        self.vector_store = await asyncio.to_thread(FAISS.from_texts, chunks, self.embeddings)

    async def generate_marketing_action(self, laravel_data: dict, rag_context: str) -> str:
        web_tool = Tool.from_google_search_retrieval(grounding.GoogleSearchRetrieval())
        prompt = f"""
        Данные Laravel (MarketingAnalyticsSnapshot): {laravel_data}
        База знаний Workspace: {rag_context}
        Сделай выжимку, найди тренды через Web Search и предложи акцию с промптом для баннера.
        """
        response = await self.llm.generate_content_async(prompt, tools=[web_tool])
        return response.text

    async def get_rag_context(self, query: str, k: int = 3) -> str:
        if not self.vector_store:
            return ""
        docs = await asyncio.to_thread(self.vector_store.similarity_search, query, k=k)
        return "\n".join([d.page_content for d in docs])

ai_service = AIService()
