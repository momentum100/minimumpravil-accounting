import json
import os
from typing import Dict, Any

class Language:
    def __init__(self, lang_code: str = 'ru'):
        self.lang_code = lang_code
        self.strings: Dict[str, str] = {}
        self.load_language()
    
    def load_language(self):
        """Load language strings from JSON file"""
        try:
            lang_file = os.path.join(
                os.path.dirname(os.path.dirname(__file__)), 
                'lang', 
                f'{self.lang_code}.json'
            )
            
            with open(lang_file, 'r', encoding='utf-8') as f:
                self.strings = json.load(f)
                
        except FileNotFoundError:
            print(f"Language file {self.lang_code}.json not found")
            self.strings = {}
        except json.JSONDecodeError:
            print(f"Invalid JSON in language file {self.lang_code}.json")
            self.strings = {}
    
    def get(self, key: str, **kwargs) -> str:
        """Get localized string with optional formatting"""
        text = self.strings.get(key, f"[Missing: {key}]")
        
        if kwargs:
            try:
                return text.format(**kwargs)
            except KeyError as e:
                print(f"Missing format key {e} for string '{key}'")
                return text
        
        return text

# Global language instance
lang = Language('ru') 