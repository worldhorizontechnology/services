import httpx
from pathlib import Path
from fastapi import FastAPI, Request, BackgroundTasks, HTTPException
from fastapi.responses import FileResponse, PlainTextResponse
from langchain_core.messages import HumanMessage
from pydantic import BaseModel, Field, ValidationError
from app.agent import build_agent_graph
from app.config import META_PAGE_ACCESS_TOKEN, META_VERIFY_TOKEN, require

app = FastAPI(title="Instagram LLM Microservice Client")


class IncomingMessage(BaseModel):
    sender_id: str = Field(min_length=1, max_length=255)
    text: str = Field(min_length=1, max_length=4000)
    ad_id: str = Field(default="none", max_length=255)
    interaction_type: str = Field(default="direct_message", max_length=100)


class AgentReply(BaseModel):
    text: str = Field(min_length=1, max_length=4000)


class LocalChatRequest(BaseModel):
    text: str = Field(min_length=1, max_length=4000)
    igsid: str = Field(default="local-test-user", min_length=1, max_length=255)

async def send_instagram_reply(igsid: str, text: str):
    """Dispatches the finalized agent response back to Instagram Direct API."""
    try:
        url = "https://graph.facebook.com/v18.0/me/messages"
        reply = AgentReply(text=text)
        payload = {"recipient": {"id": igsid}, "message": {"text": reply.text}}
        async with httpx.AsyncClient() as client:
            response = await client.post(
                url,
                params={"access_token": require(META_PAGE_ACCESS_TOKEN, "META_PAGE_ACCESS_TOKEN")},
                json=payload,
            )
            response.raise_for_status()
    except Exception as error:
        raise RuntimeError(f"Instagram reply failed for {igsid}: {error}") from error

async def execute_agent_workflow(igsid: str, text: str, metadata: dict):
    """Executes the LangGraph pipeline with MCP tool invocation."""
    try:
        graph = await build_agent_graph()
        state = {
            "messages": [HumanMessage(content=text)],
            "igsid": igsid,
            "interaction_type": metadata.get("interaction_type", "direct_message"),
            "ad_id": metadata.get("ad_id", "none"),
            "profile_data": metadata.get("profile", {}),
            "workspace_context": "",
        }
        result = await graph.ainvoke(state)
        final_message = result["messages"][-1].content
        if isinstance(final_message, str) and final_message.strip():
            await send_instagram_reply(igsid, final_message.strip())
    except Exception as error:
        raise RuntimeError(f"Instagram agent workflow failed for {igsid}: {error}") from error


async def generate_agent_response(igsid: str, text: str) -> str:
    try:
        graph = await build_agent_graph()
        result = await graph.ainvoke({
            "messages": [HumanMessage(content=text)],
            "igsid": igsid,
            "interaction_type": "local_test",
            "ad_id": "local_test",
            "profile_data": {"id": igsid},
            "workspace_context": "",
        })
        content = result["messages"][-1].content
        return AgentReply(text=content).text
    except Exception as error:
        raise RuntimeError(f"Local agent response failed for {igsid}: {error}") from error


@app.get("/local-chat", response_class=FileResponse)
async def local_chat_page():
    return FileResponse(Path(__file__).resolve().parents[1] / "local_chat.html")


@app.post("/local-chat/message", response_model=AgentReply)
async def local_chat_message(payload: LocalChatRequest):
    try:
        return AgentReply(text=await generate_agent_response(payload.igsid, payload.text.strip()))
    except Exception as error:
        raise HTTPException(status_code=500, detail=str(error)) from error

@app.get("/webhook")
async def verify_webhook(request: Request):
    """Handles Meta Webhook Verification challenge."""
    try:
        mode = request.query_params.get("hub.mode")
        token = request.query_params.get("hub.verify_token")
        challenge = request.query_params.get("hub.challenge")
        if mode == "subscribe" and token == require(META_VERIFY_TOKEN, "META_VERIFY_TOKEN") and challenge:
            return PlainTextResponse(content=challenge, status_code=200)
        raise HTTPException(status_code=403, detail="Verification failed")
    except HTTPException:
        raise
    except Exception as error:
        raise HTTPException(status_code=500, detail=f"Webhook verification failed: {error}") from error

@app.post("/webhook")
async def handle_webhook(request: Request, background_tasks: BackgroundTasks):
    """Receives incoming messages and delegates execution to background task."""
    try:
        body = await request.json()
    except ValueError as error:
        raise HTTPException(status_code=400, detail="Request body must be valid JSON.") from error
    
    try:
        if body.get("object") != "instagram":
            return {"status": "ignored"}

        for entry in body.get("entry", []):
            for msg in entry.get("messaging", []):
                sender_id = msg.get("sender", {}).get("id")

                if not sender_id or msg.get("message", {}).get("is_echo"):
                    continue

                text = msg.get("message", {}).get("text", "")
                if not isinstance(text, str) or not text.strip():
                    continue

                referral = msg.get("referral", {}) or msg.get("postback", {}).get("referral", {})
                try:
                    incoming = IncomingMessage(
                        sender_id=sender_id,
                        text=text.strip(),
                        ad_id=str(referral.get("ad_id", "none")),
                        interaction_type=str(referral.get("type", "direct_message")),
                    )
                except ValidationError:
                    continue

                background_tasks.add_task(
                    execute_agent_workflow,
                    incoming.sender_id,
                    incoming.text,
                    {
                        "ad_id": incoming.ad_id,
                        "interaction_type": incoming.interaction_type,
                        "profile": {"id": incoming.sender_id},
                    },
                )
            
        return {"status": "ok"}
    except HTTPException:
        raise
    except Exception as error:
        raise HTTPException(status_code=500, detail=f"Instagram webhook processing failed: {error}") from error
