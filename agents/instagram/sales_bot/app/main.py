import os
import httpx
from fastapi import FastAPI, Request, BackgroundTasks, HTTPException
from langchain_core.messages import HumanMessage
from app.agent import build_agent_graph

app = FastAPI(title="Instagram LLM Microservice Client")

META_VERIFY_TOKEN = os.getenv("META_VERIFY_TOKEN")
META_PAGE_ACCESS_TOKEN = os.getenv("META_PAGE_ACCESS_TOKEN")

async def send_instagram_reply(igsid: str, text: str):
    """Dispatches the finalized agent response back to Instagram Direct API."""
    url = "https://graph.facebook.com/v18.0/me/messages"
    payload = {
        "recipient": {"id": igsid},
        "message": {"text": text}
    }
    async with httpx.AsyncClient() as client:
        await client.post(
            url, 
            params={"access_token": META_PAGE_ACCESS_TOKEN}, 
            json=payload
        )

async def execute_agent_workflow(igsid: str, text: str, metadata: dict):
    """Executes the LangGraph pipeline with MCP tool invocation."""
    graph = await build_agent_graph()
    
    state = {
        "messages": [HumanMessage(content=text)],
        "igsid": igsid,
        "interaction_type": metadata.get("interaction_type", "direct_message"),
        "ad_id": metadata.get("ad_id", "none"),
        "profile_data": metadata.get("profile", {})
    }
    
    result = await graph.ainvoke(state)
    
    final_message = result["messages"][-1].content
    if final_message:
        await send_instagram_reply(igsid, str(final_message))

@app.get("/webhook")
async def verify_webhook(request: Request):
    """Handles Meta Webhook Verification challenge."""
    mode = request.query_params.get("hub.mode")
    token = request.query_params.get("hub.verify_token")
    challenge = request.query_params.get("hub.challenge")

    if mode == "subscribe" and token == META_VERIFY_TOKEN:
        return int(challenge)
    raise HTTPException(status_code=403, detail="Verification failed")

@app.post("/webhook")
async def handle_webhook(request: Request, background_tasks: BackgroundTasks):
    """Receives incoming messages and delegates execution to background task."""
    body = await request.json()
    
    if body.get("object") != "instagram":
        return {"status": "ignored"}

    for entry in body.get("entry", []):
        for msg in entry.get("messaging", []):
            sender_id = msg.get("sender", {}).get("id")
            
            if not sender_id or msg.get("message", {}).get("is_echo"):
                continue
            
            text = msg.get("message", {}).get("text", "")
            
            referral = msg.get("referral", {}) or msg.get("postback", {}).get("referral", {})
            metadata = {
                "ad_id": referral.get("ad_id", "none"),
                "interaction_type": referral.get("type", "direct_message"),
                "profile": {"id": sender_id}
            }
            
            # Offload heavy LLM/MCP execution to background process
            background_tasks.add_task(
                execute_agent_workflow,
                sender_id, text, metadata
            )
            
    return {"status": "ok"}
