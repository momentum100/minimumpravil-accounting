import aiohttp
import asyncio
import logging
from typing import Dict, Any, Optional
from datetime import datetime
from ..config.config import API_ENDPOINT, API_KEY, INTERNAL_API_USER_ID

logger = logging.getLogger(__name__)

class APIClient:
    def __init__(self):
        self.base_url = API_ENDPOINT
        self.api_key = API_KEY
        self.api_user_id = INTERNAL_API_USER_ID
        self.session: Optional[aiohttp.ClientSession] = None
    
    async def _get_session(self) -> aiohttp.ClientSession:
        """Get or create aiohttp session"""
        if self.session is None or self.session.closed:
            self.session = aiohttp.ClientSession(
                timeout=aiohttp.ClientTimeout(total=30),
                headers={
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': f'Bearer {self.api_key}',
                    'X-API-Key': self.api_key
                }
            )
        return self.session
    
    async def close(self):
        """Close the session"""
        if self.session and not self.session.closed:
            await self.session.close()
    
    async def submit_expense(self, username: str, category: str, amount: int, 
                           price_per_one: float, telegram_user_id: int) -> Dict[str, Any]:
        """Submit expense to bulk-expenses endpoint"""
        # Format data according to API specification
        data = {
            "expense_records": [
                {
                    "buyer_username": username,
                    "category": category,
                    "quantity": amount,
                    "tariff": price_per_one,
                    "comment": f"From Telegram Bot - User ID: {telegram_user_id} (API User: {self.api_user_id})"
                }
            ]
        }
        
        return await self._make_request('POST', 'bulk-expenses', data)
    
    async def submit_transfer(self, from_username: str, to_username: str, 
                            amount: float, comment: str, authorized_by: int = None) -> Dict[str, Any]:
        """Submit transfer to the new single transfer endpoint"""
        # Use configured API user ID if no specific authorized_by is provided
        effective_user_id = authorized_by if authorized_by is not None else self.api_user_id
        
        data = {
            'from_user': from_username,
            'to_user': to_username,
            'amount': amount,
            'description': comment if comment else f'Transfer from Telegram Bot (API User: {effective_user_id})'
        }
        
        logger.info(f"Submitting transfer via API User ID: {effective_user_id}")
        return await self._make_request('POST', 'transfer', data)
    
    async def _make_request(self, method: str, endpoint: str, data: Dict[str, Any]) -> Dict[str, Any]:
        """Make HTTP request to API"""
        session = await self._get_session()
        url = f"{self.base_url}{endpoint}"
        
        try:
            logger.info(f"Making {method} request to {url}")
            logger.debug(f"Request data: {data}")
            logger.debug(f"Using API key: {self.api_key[:10]}...")
            
            async with session.request(method, url, json=data) as response:
                response_text = await response.text()
                logger.info(f"Response status: {response.status}")
                logger.debug(f"Response text: {response_text}")
                
                if response.status == 200 or response.status == 201:
                    try:
                        result = await response.json()
                        return {
                            'success': True,
                            'data': result,
                            'status': response.status
                        }
                    except aiohttp.ContentTypeError:
                        return {
                            'success': True,
                            'data': {'message': 'Success'},
                            'status': response.status
                        }
                else:
                    try:
                        error_data = await response.json()
                        error_message = error_data.get('message', f'HTTP {response.status}')
                    except:
                        error_message = f'HTTP {response.status}: {response_text}'
                    
                    return {
                        'success': False,
                        'error': error_message,
                        'status': response.status
                    }
        
        except aiohttp.ClientError as e:
            logger.error(f"Network error: {e}")
            return {
                'success': False,
                'error': 'Network error',
                'exception': str(e)
            }
        except Exception as e:
            logger.error(f"Unexpected error: {e}")
            return {
                'success': False,
                'error': 'Unknown error',
                'exception': str(e)
            }

# Global API client instance
api_client = APIClient() 