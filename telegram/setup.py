#!/usr/bin/env python3
"""
Setup script for Telegram Finance Bot
"""

import subprocess
import sys
import os

def install_requirements():
    """Install Python requirements"""
    print("📦 Installing Python requirements...")
    try:
        subprocess.check_call([sys.executable, "-m", "pip", "install", "-r", "requirements.txt"])
        print("✅ Requirements installed successfully!")
    except subprocess.CalledProcessError as e:
        print(f"❌ Error installing requirements: {e}")
        return False
    return True

def create_venv():
    """Create virtual environment"""
    print("🐍 Creating virtual environment...")
    try:
        subprocess.check_call([sys.executable, "-m", "venv", "venv"])
        print("✅ Virtual environment created!")
        
        # Provide activation instructions
        if os.name == 'nt':  # Windows
            print("🔧 To activate virtual environment:")
            print("   venv\\Scripts\\activate")
        else:  # Linux/Mac
            print("🔧 To activate virtual environment:")
            print("   source venv/bin/activate")
            
    except subprocess.CalledProcessError as e:
        print(f"❌ Error creating virtual environment: {e}")
        return False
    return True

def check_config():
    """Check configuration file"""
    config_path = "src/config/config.py"
    if not os.path.exists(config_path):
        print(f"❌ Configuration file not found: {config_path}")
        return False
    
    print("✅ Configuration file found")
    print("⚠️  Please verify your bot token and admin IDs in src/config/config.py")
    return True

def main():
    """Main setup function"""
    print("🤖 Telegram Finance Bot Setup")
    print("=" * 30)
    
    # Check if we're in the right directory
    if not os.path.exists("main.py"):
        print("❌ Please run this script from the telegram directory")
        return
    
    # Create virtual environment
    if not os.path.exists("venv"):
        if not create_venv():
            return
    else:
        print("✅ Virtual environment already exists")
    
    # Install requirements
    if not install_requirements():
        return
    
    # Check configuration
    if not check_config():
        return
    
    print("\n🎉 Setup completed successfully!")
    print("\n📋 Next steps:")
    print("1. Activate virtual environment (see instructions above)")
    print("2. Verify configuration in src/config/config.py")
    print("3. Start the bot: python main.py")

if __name__ == "__main__":
    main() 