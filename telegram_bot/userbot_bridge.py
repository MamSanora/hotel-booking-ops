from telethon import TelegramClient, events
import re
import logging

# Set up logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(message)s')

# ---------------------------------------------------------
# CONFIGURATION
# ---------------------------------------------------------

# 1. Get these from https://my.telegram.org
API_ID = 12345678  # Replace with your API ID (integer)
API_HASH = 'your_api_hash_here' # Replace with your API Hash (string)

# 2. The username of the ABA Bot you want to listen to
ABA_BOT_USERNAME = 'ababank_bot' 

# 3. The Chat ID of your DARA MEAS TEST OPERATION Group Chat (Usually starts with -100)
HOTEL_GROUP_ID = -1001234567890 

# ---------------------------------------------------------

# Initialize the client (this creates a 'hotel_bridge.session' file)
client = TelegramClient('hotel_bridge', API_ID, API_HASH)

@client.on(events.NewMessage(chats=HOTEL_GROUP_ID))
async def payment_handler(event):
    """
    Listens for any new messages inside the DARA MEAS TEST OPERATION group.
    If the message was sent by the ABA Bot and looks like a payment, it relays it.
    """
    # Check if the sender is actually the ABA Bot
    sender = await event.get_sender()
    if not sender or sender.username != ABA_BOT_USERNAME:
        return # Ignore messages sent by humans or other bots
        
    message_text = event.message.message
    
    # Optional: Log the incoming message for debugging
    logging.info(f"Received message from ABA Bot in group: {message_text[:50]}...")
    
    # Check if the message contains a booking reference (BK-)
    if "BK-" in message_text:
        logging.info("[INFO] Found a booking reference! Relaying to Hotel Group...")
        
        try:
            # Send a clean relay message to the exact same group chat
            # This allows the Laravel Hotel Bot to finally see the text!
            relay_text = f"[SYSTEM RELAY] from ABA: {message_text}"
            await client.send_message(HOTEL_GROUP_ID, relay_text)
            logging.info("[SUCCESS] Relayed payment notification to the Laravel webhook!")
        except Exception as e:
            logging.error(f"[ERROR] Failed to relay message: {e}")
    else:
        logging.info("No booking reference found. Ignoring message.")

if __name__ == '__main__':
    logging.info("Starting Hotel Payment Bridge Userbot...")
    client.start()
    logging.info("[STARTED] Userbot is running and listening for ABA payments!")
    client.run_until_disconnected()
