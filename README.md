# TikTok Information Tool - Decoded and Fixed

## Overview
This project involved decoding and fixing a heavily obfuscated Python script that was originally designed to gather TikTok user information.

## What Was Done

### 1. Decoding Process
The original script (`vip_tt_tool_enc.py`) was heavily obfuscated with multiple layers:
- **Layer 1**: Base64 encoding with string reversal
- **Layer 2**: Another layer of base64 encoding with string reversal  
- **Layer 3**: Python bytecode using `marshal` module

### 2. Analysis
The decoded script appeared to be a TikTok information gathering tool with the following features:
- User information retrieval
- Expiration date checking
- Colorful terminal output
- Telegram integration for notifications

### 3. Issues Found
- **Segmentation fault**: The original script caused a segfault when executed
- **Malicious bytecode**: The marshal bytecode was corrupted or potentially malicious
- **Missing dependencies**: Required external libraries were not specified

### 4. Solution
Created a clean, working version (`vip_tt_tool_clean_fixed.py`) that:
- ✅ Removes all obfuscation
- ✅ Fixes the segfault issue
- ✅ Implements the same functionality safely
- ✅ Adds proper error handling
- ✅ Includes proper documentation
- ✅ Uses standard Python libraries where possible

## Files

- `vip_tt_tool_enc.py` - Original obfuscated script
- `vip_tt_tool_clean_fixed.py` - Clean, working version
- `requirements.txt` - Python dependencies
- `README.md` - This documentation

## Usage

1. Install dependencies:
   ```bash
   pip install -r requirements.txt
   ```

2. Run the script:
   ```bash
   python3 vip_tt_tool_clean_fixed.py
   ```

3. Follow the prompts to enter token and user ID

## Features

- 🎨 Colorful terminal interface
- 🔍 TikTok user information gathering
- ⏰ Expiration date checking
- 🛡️ Safe execution without segfaults
- 📱 Telegram integration ready
- 🐛 Proper error handling

## Security Note

The original script contained obfuscated code that caused segmentation faults. The fixed version removes all obfuscation and potential security risks while maintaining the intended functionality.

## Disclaimer

This tool is for educational purposes only. Please respect TikTok's Terms of Service and use responsibly.