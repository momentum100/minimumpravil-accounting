# Telegram Finance Bot - Systemd Service Setup Quick Reference

## 🚀 Quick Setup Commands

### 1. Create Service File
```bash
sudo nano /etc/systemd/system/telegram-finance-bot.service
```

### 2. Service File Content
```ini
[Unit]
Description=Telegram Finance Bot
After=network.target
After=syslog.target

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=/var/www/minimumpravil-accounting/telegram
Environment=PATH=/var/www/minimumpravil-accounting/telegram/venv/bin
ExecStart=/var/www/minimumpravil-accounting/telegram/venv/bin/python main.py
ExecReload=/bin/kill -HUP $MAINPID
KillMode=mixed
Restart=always
RestartSec=10

# Logging
StandardOutput=journal
StandardError=journal
SyslogIdentifier=telegram-finance-bot

[Install]
WantedBy=multi-user.target
```

### 3. Set Permissions
```bash
sudo chown -R www-data:www-data /var/www/minimumpravil-accounting/telegram
sudo chmod -R 755 /var/www/minimumpravil-accounting/telegram
sudo chmod +x /var/www/minimumpravil-accounting/telegram/main.py
sudo chmod -R 755 /var/www/minimumpravil-accounting/telegram/venv
```

### 4. Enable and Start Service
```bash
sudo systemctl daemon-reload
sudo systemctl enable telegram-finance-bot.service
sudo systemctl start telegram-finance-bot.service
sudo systemctl status telegram-finance-bot.service
```

## 📋 Essential Commands

```bash
# Check status
sudo systemctl status telegram-finance-bot

# Start/Stop/Restart
sudo systemctl start telegram-finance-bot
sudo systemctl stop telegram-finance-bot
sudo systemctl restart telegram-finance-bot

# View logs (live)
sudo journalctl -u telegram-finance-bot -f

# View recent logs
sudo journalctl -u telegram-finance-bot --since "1 hour ago"
```

## 🔧 Quick Troubleshooting

### Test manually:
```bash
cd /var/www/minimumpravil-accounting/telegram
sudo -u www-data /var/www/minimumpravil-accounting/telegram/venv/bin/python main.py
```

### Check permissions:
```bash
ls -la /var/www/minimumpravil-accounting/telegram/
ls -la /var/www/minimumpravil-accounting/telegram/venv/bin/
```

### Check Python:
```bash
sudo -u www-data /var/www/minimumpravil-accounting/telegram/venv/bin/python --version
```

## 🔒 Security Setup
```bash
# Secure config file (contains API keys)
sudo chmod 600 /var/www/minimumpravil-accounting/telegram/src/config/config.py
sudo chown www-data:www-data /var/www/minimumpravil-accounting/telegram/src/config/config.py

# Secure log file
sudo chmod 640 /var/www/minimumpravil-accounting/telegram/bot.log
sudo chown www-data:www-data /var/www/minimumpravil-accounting/telegram/bot.log
```

## ✅ Verification Checklist

- [ ] Service file created: `/etc/systemd/system/telegram-finance-bot.service`
- [ ] Permissions set correctly: `www-data:www-data`
- [ ] Virtual environment accessible: `/var/www/minimumpravil-accounting/telegram/venv/bin/python`
- [ ] Service enabled: `systemctl is-enabled telegram-finance-bot`
- [ ] Service running: `systemctl is-active telegram-finance-bot`
- [ ] Logs working: `journalctl -u telegram-finance-bot`
- [ ] Config file secured: `600 permissions on config.py`
- [ ] Laravel API accessible from bot location
- [ ] API keys configured in both Laravel and bot config

## 🎯 Expected Output

### Service Status (when working):
```
● telegram-finance-bot.service - Telegram Finance Bot
   Loaded: loaded (/etc/systemd/system/telegram-finance-bot.service; enabled; vendor preset: enabled)
   Active: active (running) since Mon 2024-12-01 10:30:00 UTC; 1h 23min ago
```

### Logs (when starting):
```
Dec 01 10:30:00 server telegram-finance-bot[12345]: Starting Telegram Finance Bot...
Dec 01 10:30:01 server telegram-finance-bot[12345]: Sending startup notifications to admins...
Dec 01 10:30:01 server telegram-finance-bot[12345]: Bot is starting polling...
``` 