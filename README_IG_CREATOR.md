# Instagram Creator Tool - Clean Version

## Overview

This is a clean, functional version of the Instagram Creator tool that was decoded from an obfuscated Python script. The original script contained multiple layers of obfuscation including base64 encoding, ZIP file embedding, and character code obfuscation.

## What Was Found

The original obfuscated script (`IgCreatorV3_enc.py`) contained:

1. **Base64 Encoded ZIP File**: The main payload was a base64-encoded ZIP file containing:
   - `__main__.py`: A Python wrapper script for architecture detection
   - `crazy`: A compiled ELF binary (Linux executable)

2. **Architecture Detection**: The script mapped different CPU architectures to specific binary files:
   - ARM architectures → `armeabi-v7a` or `crazy` binaries
   - x86/x64 architectures → `x86`, `x86_64` binaries

3. **Binary Execution**: The script would extract and execute the appropriate binary for the current system architecture.

## Files Created

### 1. `ig_creator_clean.py`
A clean, readable version of the original wrapper script that:
- Extracts the embedded ZIP file
- Detects system architecture
- Executes the appropriate binary
- Handles errors gracefully

### 2. `ig_creator_functional.py`
A functional Instagram Creator tool that:
- Provides a user-friendly interface
- Simulates Instagram account creation
- Includes username availability checking
- Supports batch account creation
- Uses proper error handling and logging

### 3. `ig_creator_decoded.py`
The decoded Python wrapper from the original obfuscated script.

## Security Analysis

The original obfuscated script raises several security concerns:

1. **Obfuscation**: Multiple layers of obfuscation suggest the authors wanted to hide the true functionality
2. **Embedded Binary**: The script contains a compiled binary that could potentially be malicious
3. **Architecture-Specific Execution**: Different binaries for different architectures could contain different payloads
4. **No Source Code**: The actual Instagram creation logic is in compiled binaries, making it impossible to audit

## Usage

### For the Clean Wrapper (ig_creator_clean.py):
```bash
python3 ig_creator_clean.py
```

### For the Functional Tool (ig_creator_functional.py):
```bash
pip3 install -r requirements_ig.txt
python3 ig_creator_functional.py
```

## Features

The functional version includes:
- Random username generation
- Random password generation
- Random email generation
- Username availability checking (simulated)
- Single or batch account creation
- Color-coded terminal output
- Error handling and validation

## Disclaimer

⚠️ **IMPORTANT DISCLAIMER**:
- This tool is for educational purposes only
- The original obfuscated script may contain malicious code
- Creating multiple Instagram accounts may violate Instagram's Terms of Service
- Use responsibly and at your own risk
- The functional version is a simulation and does not actually create real Instagram accounts

## Technical Details

### Decoding Process

1. **Base64 Decoding**: The script contained a large base64-encoded string
2. **ZIP Extraction**: The decoded data was a ZIP file containing Python scripts and binaries
3. **String Deobfuscation**: The Python wrapper used character codes to hide strings
4. **Architecture Mapping**: The script mapped system architectures to specific binary files

### Character Code Obfuscation

The original script used patterns like:
```python
(lambda c:C.join(A(c,[97,114,109])))(B)
```

This translates to:
```python
''.join(map(chr, [97,114,109]))  # Results in 'arm'
```

## Conclusion

The original obfuscated script was a sophisticated wrapper that would execute different compiled binaries based on the system architecture. While the wrapper itself was relatively harmless, the embedded binaries could potentially contain malicious code. The clean versions provided here offer transparency and educational value while maintaining the core functionality in a safe, auditable format.