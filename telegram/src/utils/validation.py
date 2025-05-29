from typing import Tuple, Optional, Dict, Any
from .lang import lang

def validate_expense_input(lines: list[str]) -> Tuple[bool, Optional[str], Optional[Dict[str, Any]]]:
    """
    Validate expense input format
    Returns: (is_valid, error_message, parsed_data)
    """
    if len(lines) != 4:
        return False, lang.get('invalid_format'), None
    
    username, category, amount_str, price_str = [line.strip() for line in lines]
    
    # Check for empty fields
    if not username:
        return False, lang.get('empty_field', line=1), None
    if not category:
        return False, lang.get('empty_field', line=2), None
    if not amount_str:
        return False, lang.get('empty_field', line=3), None
    if not price_str:
        return False, lang.get('empty_field', line=4), None
    
    # Validate username length
    if len(username) > 50:
        return False, "❌ Имя пользователя слишком длинное (максимум 50 символов)", None
    
    # Validate category length
    if len(category) > 100:
        return False, "❌ Категория слишком длинная (максимум 100 символов)", None
    
    # Validate amount (integer)
    try:
        amount = int(amount_str)
        if amount <= 0:
            return False, lang.get('invalid_amount', line=3), None
    except ValueError:
        return False, lang.get('invalid_amount', line=3), None
    
    # Validate price (float)
    try:
        price = float(price_str)
        if price <= 0:
            return False, lang.get('invalid_price', line=4), None
        # Check for reasonable decimal places
        if len(price_str.split('.')[-1]) > 2 if '.' in price_str else False:
            return False, "❌ Цена не может иметь больше 2 знаков после запятой", None
    except ValueError:
        return False, lang.get('invalid_price', line=4), None
    
    # Check reasonable limits
    if amount > 10000:
        return False, "❌ Количество слишком большое (максимум 10,000)", None
    if price > 100000:
        return False, "❌ Цена слишком большая (максимум 100,000)", None
    
    return True, None, {
        'username': username,
        'category': category,
        'amount': amount,
        'price_per_one': price
    }

def validate_transfer_input(lines: list[str]) -> Tuple[bool, Optional[str], Optional[Dict[str, Any]]]:
    """
    Validate transfer input format
    Returns: (is_valid, error_message, parsed_data)
    """
    if len(lines) != 4:
        return False, lang.get('invalid_format'), None
    
    from_user, to_user, amount_str, comment = [line.strip() for line in lines]
    
    # Check for empty required fields
    if not from_user:
        return False, lang.get('empty_field', line=1), None
    if not to_user:
        return False, lang.get('empty_field', line=2), None
    if not amount_str:
        return False, lang.get('empty_field', line=3), None
    # Comment (line 4) is optional
    
    # Validate username lengths
    if len(from_user) > 50:
        return False, "❌ Имя отправителя слишком длинное (максимум 50 символов)", None
    if len(to_user) > 50:
        return False, "❌ Имя получателя слишком длинное (максимум 50 символов)", None
    
    # Validate comment length
    if len(comment) > 200:
        return False, "❌ Комментарий слишком длинный (максимум 200 символов)", None
    
    # Validate amount (float)
    try:
        amount = float(amount_str)
        if amount <= 0:
            return False, lang.get('invalid_transfer_amount', line=3), None
        # Check for reasonable decimal places
        if len(amount_str.split('.')[-1]) > 2 if '.' in amount_str else False:
            return False, "❌ Сумма не может иметь больше 2 знаков после запятой", None
    except ValueError:
        return False, lang.get('invalid_transfer_amount', line=3), None
    
    # Check reasonable limits
    if amount > 1000000:
        return False, "❌ Сумма перевода слишком большая (максимум 1,000,000)", None
    
    # Prevent self-transfer
    if from_user.lower() == to_user.lower():
        return False, "❌ Нельзя переводить самому себе", None
    
    return True, None, {
        'from_username': from_user,
        'to_username': to_user,
        'amount': amount,
        'comment': comment if comment else ''
    } 