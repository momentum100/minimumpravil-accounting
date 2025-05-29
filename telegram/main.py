import asyncio
import logging
import sys
from aiogram import Bot, Dispatcher
from aiogram.client.default import DefaultBotProperties
from aiogram.enums import ParseMode

from src.config.config import BOT_API_KEY
from src.handlers import router, notify_admins_bot_started
from src.utils.api_client import api_client

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('bot.log', encoding='utf-8'),
        logging.StreamHandler(sys.stdout)
    ]
)

logger = logging.getLogger(__name__)

async def main():
    """Main function to run the bot"""
    logger.info("Starting Telegram Finance Bot...")
    
    # Initialize Bot instance
    bot = Bot(
        token=BOT_API_KEY,
        default=DefaultBotProperties(parse_mode=ParseMode.HTML)
    )
    
    # Initialize Dispatcher
    dp = Dispatcher()
    
    # Include router
    dp.include_router(router)
    
    try:
        # Notify admins that bot is starting
        logger.info("Sending startup notifications to admins...")
        await notify_admins_bot_started(bot)
        
        # Start polling
        logger.info("Bot is starting polling...")
        await dp.start_polling(bot)
    except Exception as e:
        logger.error(f"Error occurred: {e}")
    finally:
        # Close API client session
        await api_client.close()
        logger.info("Bot stopped.")

if __name__ == "__main__":
    try:
        asyncio.run(main())
    except KeyboardInterrupt:
        logger.info("Bot stopped by user")
    except Exception as e:
        logger.error(f"Unexpected error: {e}")
