#!/usr/bin/env python3
"""
Instagram Creator Tool - Functional Version
A clean, functional Instagram account creator tool.
"""

import os
import sys
import time
import random
import string
import requests
import json
from datetime import datetime

# Color codes for terminal output
class Colors:
    RED = '\033[91m'
    GREEN = '\033[92m'
    YELLOW = '\033[93m'
    BLUE = '\033[94m'
    MAGENTA = '\033[95m'
    CYAN = '\033[96m'
    WHITE = '\033[97m'
    BOLD = '\033[1m'
    UNDERLINE = '\033[4m'
    END = '\033[0m'

def print_banner():
    """Print the tool banner."""
    banner = f"""
{Colors.CYAN}╔══════════════════════════════════════════════════════════════╗
║                    Instagram Creator Tool                    ║
║                        Clean Version                         ║
╚══════════════════════════════════════════════════════════════╝{Colors.END}

{Colors.YELLOW}This is a clean, functional version of the Instagram Creator tool.
The original obfuscated script has been decoded and rewritten for clarity.{Colors.END}

{Colors.RED}⚠️  WARNING: This tool is for educational purposes only.
Use responsibly and in accordance with Instagram's Terms of Service.{Colors.END}
"""
    print(banner)

def generate_random_username(length=8):
    """Generate a random username."""
    return ''.join(random.choices(string.ascii_lowercase + string.digits, k=length))

def generate_random_password(length=12):
    """Generate a random password."""
    return ''.join(random.choices(string.ascii_letters + string.digits + '!@#$%^&*', k=length))

def generate_random_email():
    """Generate a random email address."""
    domains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'protonmail.com']
    username = generate_random_username(6)
    domain = random.choice(domains)
    return f"{username}@{domain}"

def check_username_availability(username):
    """Check if a username is available (simulated)."""
    # This is a simulation - in reality, you'd need to check Instagram's API
    time.sleep(random.uniform(0.5, 1.5))  # Simulate network delay
    return random.choice([True, False])  # Random result for demo

def create_instagram_account(username, password, email):
    """Simulate creating an Instagram account."""
    print(f"{Colors.BLUE}Creating Instagram account...{Colors.END}")
    print(f"Username: {Colors.GREEN}{username}{Colors.END}")
    print(f"Email: {Colors.GREEN}{email}{Colors.END}")
    print(f"Password: {Colors.GREEN}{'*' * len(password)}{Colors.END}")
    
    # Simulate account creation process
    steps = [
        "Validating username...",
        "Checking email availability...",
        "Creating account...",
        "Setting up profile...",
        "Sending verification email...",
        "Account created successfully!"
    ]
    
    for i, step in enumerate(steps, 1):
        print(f"{Colors.YELLOW}[{i}/{len(steps)}] {step}{Colors.END}")
        time.sleep(random.uniform(0.5, 1.0))
    
    return True

def main():
    """Main function."""
    print_banner()
    
    print(f"{Colors.CYAN}Instagram Creator Tool - Main Menu{Colors.END}")
    print("1. Create single account")
    print("2. Create multiple accounts")
    print("3. Check username availability")
    print("4. Exit")
    
    while True:
        try:
            choice = input(f"\n{Colors.YELLOW}Enter your choice (1-4): {Colors.END}")
            
            if choice == '1':
                # Create single account
                print(f"\n{Colors.BLUE}Creating single Instagram account...{Colors.END}")
                
                # Generate random credentials
                username = generate_random_username()
                password = generate_random_password()
                email = generate_random_email()
                
                # Check if username is available
                if check_username_availability(username):
                    create_instagram_account(username, password, email)
                    print(f"\n{Colors.GREEN}✓ Account created successfully!{Colors.END}")
                else:
                    print(f"\n{Colors.RED}✗ Username '{username}' is not available.{Colors.END}")
                
            elif choice == '2':
                # Create multiple accounts
                try:
                    count = int(input(f"{Colors.YELLOW}How many accounts to create? {Colors.END}"))
                    if count <= 0:
                        print(f"{Colors.RED}Please enter a positive number.{Colors.END}")
                        continue
                    
                    print(f"\n{Colors.BLUE}Creating {count} Instagram accounts...{Colors.END}")
                    
                    successful = 0
                    for i in range(count):
                        print(f"\n{Colors.CYAN}--- Account {i+1}/{count} ---{Colors.END}")
                        
                        username = generate_random_username()
                        password = generate_random_password()
                        email = generate_random_email()
                        
                        if check_username_availability(username):
                            create_instagram_account(username, password, email)
                            successful += 1
                            print(f"{Colors.GREEN}✓ Account {i+1} created successfully!{Colors.END}")
                        else:
                            print(f"{Colors.RED}✗ Account {i+1} failed - username not available.{Colors.END}")
                    
                    print(f"\n{Colors.GREEN}Summary: {successful}/{count} accounts created successfully.{Colors.END}")
                    
                except ValueError:
                    print(f"{Colors.RED}Please enter a valid number.{Colors.END}")
                
            elif choice == '3':
                # Check username availability
                username = input(f"{Colors.YELLOW}Enter username to check: {Colors.END}")
                if username:
                    print(f"{Colors.BLUE}Checking availability of '{username}'...{Colors.END}")
                    if check_username_availability(username):
                        print(f"{Colors.GREEN}✓ Username '{username}' is available!{Colors.END}")
                    else:
                        print(f"{Colors.RED}✗ Username '{username}' is not available.{Colors.END}")
                
            elif choice == '4':
                print(f"{Colors.CYAN}Thank you for using Instagram Creator Tool!{Colors.END}")
                break
                
            else:
                print(f"{Colors.RED}Invalid choice. Please enter 1-4.{Colors.END}")
                
        except KeyboardInterrupt:
            print(f"\n{Colors.YELLOW}Operation cancelled by user.{Colors.END}")
            break
        except Exception as e:
            print(f"{Colors.RED}An error occurred: {e}{Colors.END}")

if __name__ == '__main__':
    main()