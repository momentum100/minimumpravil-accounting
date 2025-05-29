import logging
from aiogram import Router, F
from aiogram.types import Message, CallbackQuery, InlineKeyboardMarkup, InlineKeyboardButton, ReplyKeyboardMarkup, KeyboardButton
from aiogram.filters import Command, CommandStart
from typing import Dict, Any

from .config.config import ADMINS
from .utils.lang import lang
from .utils.validation import validate_expense_input, validate_transfer_input
from .utils.api_client import api_client

logger = logging.getLogger(__name__)
router = Router()

# Temporary storage for confirmation data and user states
pending_confirmations: Dict[int, Dict[str, Any]] = {}
user_states: Dict[int, str] = {}  # Track what users are waiting for

def is_admin(user_id: int) -> bool:
    """Check if user is admin"""
    return user_id in ADMINS

def create_main_menu() -> InlineKeyboardMarkup:
    """Create main menu with command buttons"""
    return InlineKeyboardMarkup(inline_keyboard=[
        [InlineKeyboardButton(text=lang.get('btn_buyer_expenses'), callback_data='cmd_expenses')],
        [InlineKeyboardButton(text=lang.get('btn_buyer_transfers'), callback_data='cmd_transfers')]
    ])

def create_reply_keyboard() -> ReplyKeyboardMarkup:
    """Create persistent reply keyboard menu"""
    return ReplyKeyboardMarkup(
        keyboard=[
            [
                KeyboardButton(text=lang.get('menu_expenses')),
                KeyboardButton(text=lang.get('menu_transfers'))
            ],
            [
                KeyboardButton(text=lang.get('menu_help'))
            ]
        ],
        resize_keyboard=True,
        persistent=True
    )

def create_confirmation_keyboard() -> InlineKeyboardMarkup:
    """Create confirmation keyboard"""
    return InlineKeyboardMarkup(inline_keyboard=[
        [
            InlineKeyboardButton(text=lang.get('yes'), callback_data='confirm_yes'),
            InlineKeyboardButton(text=lang.get('no'), callback_data='confirm_no')
        ]
    ])

def create_cancel_keyboard() -> InlineKeyboardMarkup:
    """Create cancel keyboard for operations"""
    return InlineKeyboardMarkup(inline_keyboard=[
        [InlineKeyboardButton(text=lang.get('btn_cancel'), callback_data='cancel_operation')]
    ])

def create_back_keyboard() -> InlineKeyboardMarkup:
    """Create back to menu keyboard"""
    return InlineKeyboardMarkup(inline_keyboard=[
        [InlineKeyboardButton(text=lang.get('btn_back'), callback_data='back_to_menu')]
    ])

async def notify_admins_bot_started(bot):
    """Notify all admins that bot has started"""
    startup_message = lang.get('bot_started')
    
    for admin_id in ADMINS:
        try:
            await bot.send_message(admin_id, startup_message)
            logger.info(f"Startup notification sent to admin {admin_id}")
        except Exception as e:
            logger.error(f"Failed to send startup notification to admin {admin_id}: {e}")

@router.message(CommandStart())
async def start_handler(message: Message):
    """Handle /start command"""
    user_id = message.from_user.id
    
    logger.info(f"User {user_id} started the bot")
    
    if not is_admin(user_id):
        await message.answer(lang.get('unauthorized'))
        return
    
    # Clear any pending state
    if user_id in user_states:
        del user_states[user_id]
    if user_id in pending_confirmations:
        del pending_confirmations[user_id]
    
    await message.answer(
        lang.get('welcome'), 
        reply_markup=create_reply_keyboard()
    )
    await message.answer(
        "Выберите действие из меню ниже:",
        reply_markup=create_main_menu()
    )

@router.message(F.text == lang.get('menu_expenses'))
async def menu_expenses_handler(message: Message):
    """Handle menu expenses button"""
    user_id = message.from_user.id
    
    logger.info(f"User {user_id} selected expenses from menu")
    
    if not is_admin(user_id):
        await message.answer(lang.get('admin_only'))
        return
    
    # Set user state to waiting for expense data
    user_states[user_id] = 'waiting_expense_data'
    
    await message.answer(
        lang.get('expenses_prompt'),
        reply_markup=create_cancel_keyboard()
    )

@router.message(F.text == lang.get('menu_transfers'))
async def menu_transfers_handler(message: Message):
    """Handle menu transfers button"""
    user_id = message.from_user.id
    
    logger.info(f"User {user_id} selected transfers from menu")
    
    if not is_admin(user_id):
        await message.answer(lang.get('admin_only'))
        return
    
    # Set user state to waiting for transfer data
    user_states[user_id] = 'waiting_transfer_data'
    
    await message.answer(
        lang.get('transfers_prompt'),
        reply_markup=create_cancel_keyboard()
    )

@router.message(F.text == lang.get('menu_help'))
async def menu_help_handler(message: Message):
    """Handle menu help button"""
    user_id = message.from_user.id
    
    logger.info(f"User {user_id} requested help from menu")
    
    if not is_admin(user_id):
        await message.answer(lang.get('admin_only'))
        return
    
    await message.answer(lang.get('help_text'))

@router.callback_query(F.data == 'cmd_expenses')
async def expenses_button_handler(callback: CallbackQuery):
    """Handle expenses button press"""
    user_id = callback.from_user.id
    
    logger.info(f"User {user_id} selected expenses command")
    
    if not is_admin(user_id):
        await callback.answer(lang.get('admin_only'))
        return
    
    # Set user state to waiting for expense data
    user_states[user_id] = 'waiting_expense_data'
    
    await callback.message.edit_text(
        lang.get('expenses_prompt'),
        reply_markup=create_cancel_keyboard()
    )
    await callback.answer()

@router.callback_query(F.data == 'cmd_transfers')
async def transfers_button_handler(callback: CallbackQuery):
    """Handle transfers button press"""
    user_id = callback.from_user.id
    
    logger.info(f"User {user_id} selected transfers command")
    
    if not is_admin(user_id):
        await callback.answer(lang.get('admin_only'))
        return
    
    # Set user state to waiting for transfer data
    user_states[user_id] = 'waiting_transfer_data'
    
    await callback.message.edit_text(
        lang.get('transfers_prompt'),
        reply_markup=create_cancel_keyboard()
    )
    await callback.answer()

@router.callback_query(F.data == 'cancel_operation')
async def cancel_operation_handler(callback: CallbackQuery):
    """Handle cancel operation"""
    user_id = callback.from_user.id
    
    # Clear user state
    if user_id in user_states:
        del user_states[user_id]
    if user_id in pending_confirmations:
        del pending_confirmations[user_id]
    
    await callback.message.edit_text(
        lang.get('cancelled'),
        reply_markup=create_back_keyboard()
    )
    await callback.answer()

@router.callback_query(F.data == 'back_to_menu')
async def back_to_menu_handler(callback: CallbackQuery):
    """Handle back to menu"""
    user_id = callback.from_user.id
    
    # Clear any states
    if user_id in user_states:
        del user_states[user_id]
    if user_id in pending_confirmations:
        del pending_confirmations[user_id]
    
    await callback.message.edit_text(
        lang.get('welcome'),
        reply_markup=create_main_menu()
    )
    await callback.answer()

@router.message(Command('buyer_expenses'))
async def buyer_expenses_handler(message: Message):
    """Handle /buyer_expenses command (legacy support)"""
    user_id = message.from_user.id
    
    logger.info(f"User {user_id} used /buyer_expenses command (legacy)")
    
    if not is_admin(user_id):
        await message.answer(lang.get('admin_only'))
        return
    
    # Parse message text to extract the 4 lines
    text = message.text
    lines = text.split('\n')
    
    # Remove the command line
    if lines[0].startswith('/buyer_expenses'):
        lines = lines[1:]
    
    if len(lines) == 0:
        await message.answer(lang.get('expenses_help'))
        return
    
    await process_expense_data(message, lines, user_id)

@router.message(Command('buyer_transfers'))
async def buyer_transfers_handler(message: Message):
    """Handle /buyer_transfers command (legacy support)"""
    user_id = message.from_user.id
    
    logger.info(f"User {user_id} used /buyer_transfers command (legacy)")
    
    if not is_admin(user_id):
        await message.answer(lang.get('admin_only'))
        return
    
    # Parse message text to extract the 4 lines
    text = message.text
    lines = text.split('\n')
    
    # Remove the command line
    if lines[0].startswith('/buyer_transfers'):
        lines = lines[1:]
    
    if len(lines) == 0:
        await message.answer(lang.get('transfers_help'))
        return
    
    await process_transfer_data(message, lines, user_id)

async def process_expense_data(message: Message, lines: list[str], user_id: int):
    """Process expense data input"""
    # Validate input
    is_valid, error_msg, data = validate_expense_input(lines)
    
    if not is_valid:
        await message.answer(error_msg)
        if user_id in user_states and user_states[user_id] == 'waiting_expense_data':
            await message.answer(lang.get('expenses_prompt'), reply_markup=create_cancel_keyboard())
        else:
            await message.answer(lang.get('expenses_help'))
        return
    
    # Calculate total
    total = data['amount'] * data['price_per_one']
    
    # Store data for confirmation
    pending_confirmations[user_id] = {
        'type': 'expense',
        'data': data,
        'total': total
    }
    
    # Clear waiting state
    if user_id in user_states:
        del user_states[user_id]
    
    # Send confirmation message
    confirm_text = lang.get('confirm_expense',
                           username=data['username'],
                           category=data['category'],
                           amount=data['amount'],
                           price=f"{data['price_per_one']:.2f}",
                           total=f"{total:.2f}")
    
    await message.answer(confirm_text, reply_markup=create_confirmation_keyboard())

async def process_transfer_data(message: Message, lines: list[str], user_id: int):
    """Process transfer data input"""
    # Validate input
    is_valid, error_msg, data = validate_transfer_input(lines)
    
    if not is_valid:
        await message.answer(error_msg)
        if user_id in user_states and user_states[user_id] == 'waiting_transfer_data':
            await message.answer(lang.get('transfers_prompt'), reply_markup=create_cancel_keyboard())
        else:
            await message.answer(lang.get('transfers_help'))
        return
    
    # Store data for confirmation
    pending_confirmations[user_id] = {
        'type': 'transfer',
        'data': data
    }
    
    # Clear waiting state
    if user_id in user_states:
        del user_states[user_id]
    
    # Send confirmation message
    confirm_text = lang.get('confirm_transfer',
                           from_user=data['from_username'],
                           to_user=data['to_username'],
                           amount=f"{data['amount']:.2f}",
                           comment=data['comment'] or 'Нет')
    
    await message.answer(confirm_text, reply_markup=create_confirmation_keyboard())

@router.callback_query(F.data == 'confirm_yes')
async def confirm_yes_handler(callback: CallbackQuery):
    """Handle confirmation YES"""
    user_id = callback.from_user.id
    
    if user_id not in pending_confirmations:
        await callback.answer("❌ Данные для подтверждения не найдены")
        return
    
    confirmation_data = pending_confirmations[user_id]
    operation_type = confirmation_data['type']
    data = confirmation_data['data']
    
    try:
        if operation_type == 'expense':
            # Submit expense
            result = await api_client.submit_expense(
                username=data['username'],
                category=data['category'],
                amount=data['amount'],
                price_per_one=data['price_per_one'],
                telegram_user_id=user_id
            )
            
            if result['success']:
                entry_id = result.get('data', {}).get('id', 'Unknown')
                success_msg = lang.get('expense_success', entry_id=entry_id)
                await callback.message.edit_text(success_msg, reply_markup=create_back_keyboard())
                logger.info(f"Expense submitted successfully by user {user_id}")
            else:
                error_msg = lang.get('api_error', error=result.get('error', 'Unknown'))
                await callback.message.edit_text(error_msg, reply_markup=create_back_keyboard())
                logger.error(f"API error for expense: {result}")
        
        elif operation_type == 'transfer':
            # Submit transfer
            result = await api_client.submit_transfer(
                from_username=data['from_username'],
                to_username=data['to_username'],
                amount=data['amount'],
                comment=data['comment']
            )
            
            if result['success']:
                # Extract data from the new API response format
                transfer_data = result.get('data', {})
                fund_transfer_id = transfer_data.get('fund_transfer_id', 'Unknown')
                original_amount = transfer_data.get('original_amount', data['amount'])
                final_amount = transfer_data.get('final_amount', data['amount'])
                commission_applied = transfer_data.get('commission_applied', False)
                commission_rate = transfer_data.get('commission_rate')
                commission_amount = transfer_data.get('commission_amount')
                
                # Build success message with commission info if applicable
                if commission_applied and commission_rate and commission_amount:
                    success_msg = f"✅ Перевод выполнен успешно!\n\n" \
                                f"📄 ID перевода: {fund_transfer_id}\n" \
                                f"👤 От: {data['from_username']}\n" \
                                f"👤 Кому: {data['to_username']}\n" \
                                f"💰 Исходная сумма: ${original_amount:.2f}\n" \
                                f"💰 Финальная сумма: ${final_amount:.2f}\n" \
                                f"📊 Комиссия агентства: {commission_rate*100:.2f}% (${commission_amount:.2f})\n" \
                                f"📝 Описание: {data['comment'] or 'Transfer from Telegram Bot'}"
                else:
                    success_msg = f"✅ Перевод выполнен успешно!\n\n" \
                                f"📄 ID перевода: {fund_transfer_id}\n" \
                                f"👤 От: {data['from_username']}\n" \
                                f"👤 Кому: {data['to_username']}\n" \
                                f"💰 Сумма: ${final_amount:.2f}\n" \
                                f"📝 Описание: {data['comment'] or 'Transfer from Telegram Bot'}"
                
                await callback.message.edit_text(success_msg, reply_markup=create_back_keyboard())
                logger.info(f"Transfer submitted successfully by user {user_id}: {fund_transfer_id}")
            else:
                error_msg = lang.get('api_error', error=result.get('error', 'Unknown'))
                await callback.message.edit_text(error_msg, reply_markup=create_back_keyboard())
                logger.error(f"API error for transfer: {result}")
    
    except Exception as e:
        logger.error(f"Error processing confirmation: {e}")
        await callback.message.edit_text(lang.get('unknown_error'), reply_markup=create_back_keyboard())
    
    finally:
        # Clean up
        if user_id in pending_confirmations:
            del pending_confirmations[user_id]
        await callback.answer()

@router.callback_query(F.data == 'confirm_no')
async def confirm_no_handler(callback: CallbackQuery):
    """Handle confirmation NO"""
    user_id = callback.from_user.id
    
    # Clean up
    if user_id in pending_confirmations:
        del pending_confirmations[user_id]
    
    await callback.message.edit_text(lang.get('cancelled'), reply_markup=create_back_keyboard())
    await callback.answer()

@router.message()
async def message_handler(message: Message):
    """Handle all text messages based on user state"""
    user_id = message.from_user.id
    
    if not is_admin(user_id):
        await message.answer(lang.get('unauthorized'))
        return
    
    # Check if user is in a waiting state
    if user_id in user_states:
        state = user_states[user_id]
        text = message.text
        
        if text and not text.startswith('/'):
            lines = text.split('\n')
            
            if state == 'waiting_expense_data':
                await process_expense_data(message, lines, user_id)
                return
            elif state == 'waiting_transfer_data':
                await process_transfer_data(message, lines, user_id)
                return
    
    # Check if message looks like a command format but user is not in waiting state
    text = message.text
    if text and '\n' in text:
        lines = text.split('\n')
        if len(lines) >= 4:
            await message.answer("❌ Сначала выберите команду из меню")
            await message.answer(lang.get('welcome'), reply_markup=create_main_menu())
            return
    
    # Default response - show main menu
    await message.answer(lang.get('welcome'), reply_markup=create_main_menu()) 