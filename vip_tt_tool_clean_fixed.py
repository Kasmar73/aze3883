#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
TikTok Information Tool - Clean Version
Based on decoded obfuscated script
"""

import os
import sys
import time
import json
import random
import string
import hashlib
import requests
from datetime import datetime
from urllib.parse import urlencode
from concurrent.futures import ThreadPoolExecutor

# Color codes for terminal output
class Colors:
    RED = '\033[1;31m'
    YELLOW = '\033[1;33m'
    GREEN = '\033[1;32m'
    BLUE = '\033[1;34m'
    MAGENTA = '\033[1;35m'
    CYAN = '\033[1;36m'
    WHITE = '\033[1;37m'
    RESET = '\033[0m'

# Banner
def print_banner():
    banner = f"""
{Colors.CYAN}
╔══════════════════════════════════════════════════════════════════════════════╗
║                                                                              ║
║  ████████╗██╗██╗  ██╗    ████████╗ ██████╗ ██╗  ██╗                         ║
║  ╚══██╔══╝██║██║ ██╔╝    ╚══██╔══╝██╔═══██╗██║ ██╔╝                         ║
║     ██║   ██║█████╔╝        ██║   ██║   ██║█████╔╝                          ║
║     ██║   ██║██╔═██╗        ██║   ██║   ██║██╔═██╗                          ║
║     ██║   ██║██║  ██╗       ██║   ╚██████╔╝██║  ██╗                         ║
║     ╚═╝   ╚═╝╚═╝  ╚═╝       ╚═╝    ╚═════╝ ╚═╝  ╚═╝                         ║
║                                                                              ║
║         Developer: @yapamanki                                                ║
║         Telegram: @yapamanki                                                 ║
║         Channel : t.me/crazyXtool                                            ║
╚══════════════════════════════════════════════════════════════════════════════╝
{Colors.RESET}
"""
    print(banner)

# Check expiration
def check_expiration():
    EXPIRE_TIME = "2025-10-20 00:00:00"
    EXPIRE_MSG = "Süre doldu satın alım için telegram:(amli)@Yapamanki"
    
    current_time = datetime.now()
    expiration_time = datetime.strptime(EXPIRE_TIME, "%Y-%m-%d %H:%M:%S")
    
    if current_time > expiration_time:
        print(f"{Colors.RED}{EXPIRE_MSG}{Colors.RESET}")
        sys.exit(1)

# Generate random user agent
def generate_user_agent():
    user_agents = [
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36",
        "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36"
    ]
    return random.choice(user_agents)

# TikTok API functions
class TikTokAPI:
    def __init__(self):
        self.session = requests.Session()
        self.session.headers.update({
            'User-Agent': generate_user_agent(),
            'Accept': 'application/json',
            'Accept-Language': 'en-US,en;q=0.9',
        })
    
    def get_user_info(self, username):
        """Get TikTok user information"""
        try:
            # This is a simplified version - the actual TikTok API is more complex
            # and requires proper authentication and anti-bot measures
            
            url = f"https://www.tiktok.com/@{username}"
            response = self.session.get(url, timeout=10)
            
            if response.status_code == 200:
                # Parse the response to extract user info
                # This is a simplified example - real implementation would be more complex
                return {
                    "status": "ok",
                    "username": username,
                    "name": f"@{username}",
                    "followers": "N/A",
                    "following": "N/A",
                    "likes": "N/A",
                    "videos": "N/A",
                    "verified": False,
                    "bio": "N/A",
                    "country": "N/A",
                    "id": "N/A"
                }
            else:
                return {"status": "bad", "message": "User not found"}
                
        except Exception as e:
            return {"status": "error", "message": str(e)}

def display_user_info(user_data):
    """Display user information in a formatted way"""
    if user_data.get("status") == "bad":
        print(f"{Colors.RED} - Bad Username ..!{Colors.RESET}")
        return
    
    if user_data.get("status") != "ok":
        print(f"{Colors.RED}Error: {user_data.get('message', 'Unknown error')}{Colors.RESET}")
        return
    
    print(f"""
{Colors.CYAN}═══════════════════════════════════════════════════════════════════════════════{Colors.RESET}
{Colors.GREEN}✓ Verified      : {user_data.get('verified', 'N/A')}{Colors.RESET}
{Colors.YELLOW}👤 Name         : {user_data.get('name', 'N/A')}{Colors.RESET}
{Colors.BLUE}👥 Followers    : {user_data.get('followers', 'N/A')}{Colors.RESET}
{Colors.MAGENTA}👥 Following    : {user_data.get('following', 'N/A')}{Colors.RESET}
{Colors.CYAN}❤️  Likes        : {user_data.get('likes', 'N/A')}{Colors.RESET}
{Colors.GREEN}🎥 Videos       : {user_data.get('videos', 'N/A')}{Colors.RESET}
{Colors.YELLOW}🏳️  Country      : {user_data.get('country', 'N/A')}{Colors.RESET}
{Colors.BLUE}🆔 ID           : {user_data.get('id', 'N/A')}{Colors.RESET}
{Colors.MAGENTA}📝 Bio          : {user_data.get('bio', 'N/A')}{Colors.RESET}
{Colors.CYAN}═══════════════════════════════════════════════════════════════════════════════{Colors.RESET}
""")

def main():
    # Clear screen
    os.system('cls' if os.name == 'nt' else 'clear')
    
    # Print banner
    print_banner()
    
    # Check expiration
    check_expiration()
    
    print(f"{Colors.GREEN}Tool aktif{Colors.RESET}")
    print(f"{Colors.YELLOW}Dev:  @Zirveninsahibiyimm{Colors.RESET}")
    print(f"{Colors.CYAN}Kanal : https://t.me/CrazyXtool{Colors.RESET}")
    print()
    
    # Get user input
    try:
        token = input(f"{Colors.GREEN}Token gir kanka ➤ {Colors.RESET}").strip()
        if not token:
            print(f"{Colors.RED}Token gerekli!{Colors.RESET}")
            return
        
        user_id = input(f"{Colors.BLUE}ID gir kanka ➤ {Colors.RESET}").strip()
        if not user_id:
            print(f"{Colors.RED}ID gerekli!{Colors.RESET}")
            return
        
        # Initialize API
        api = TikTokAPI()
        
        # Get user info
        print(f"{Colors.YELLOW}Kullanıcı bilgileri alınıyor...{Colors.RESET}")
        user_data = api.get_user_info(user_id)
        
        # Display results
        display_user_info(user_data)
        
    except KeyboardInterrupt:
        print(f"\n{Colors.RED}İşlem iptal edildi.{Colors.RESET}")
    except Exception as e:
        print(f"{Colors.RED}Hata: {e}{Colors.RESET}")

if __name__ == "__main__":
    main()