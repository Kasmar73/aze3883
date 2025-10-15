#!/usr/bin/env python3
"""
Instagram Creator Tool - Clean Version
Decoded and cleaned from the obfuscated original script.
"""

import os
import sys
import platform
import tempfile
import zipfile
import shutil
import subprocess

def print_message(message):
    """Print a message to the console."""
    print(message)

def get_architecture_mapping():
    """Get the mapping of system architectures to binary names."""
    return {
        'armv7l': 'armeabi-v7a',
        'armv8l': 'armeabi-v7a', 
        'arm': 'armeabi-v7a',
        'aarch64': 'crazy',
        'arm64': 'crazy',
        'x86': 'x86',
        'i686': 'x86',
        'x86_64': 'x86_64',
        'amd64': 'x86_64'
    }

def extract_and_run():
    """Extract the embedded binary and run it."""
    # Get the directory of the current script
    script_dir = os.path.dirname(os.path.abspath(sys.argv[0]))
    temp_dir = tempfile.mkdtemp()
    
    try:
        # Get the path to the current script
        script_path = os.path.abspath(sys.argv[0])
        
        # Extract the ZIP file from the script
        with zipfile.ZipFile(script_path, 'r') as zip_file:
            zip_file.extractall(temp_dir)
        
        # Get the current system architecture
        current_arch = platform.machine()
        arch_mapping = get_architecture_mapping()
        
        # Check if architecture is supported
        if current_arch not in arch_mapping:
            print_message(f"Unsupported architecture: {current_arch}")
            sys.exit(1)
        
        # Get the binary name for this architecture
        binary_name = arch_mapping[current_arch]
        binary_path = os.path.join(temp_dir, binary_name)
        
        # Check if the binary exists
        if not os.path.exists(binary_path):
            print_message(f"Expected binary for {current_arch} not found")
            sys.exit(1)
        
        # Make the binary executable
        os.chmod(binary_path, 0o755)
        
        # Change to the script directory
        os.chdir(script_dir)
        
        # Prepare the command to run the binary
        # Set up environment variables for Python execution
        env_command = (
            f"export LD_LIBRARY_PATH=$LD_LIBRARY_PATH:{sys.prefix}/lib && "
            f"export PYTHONHOME={sys.prefix} && "
            f"export PYTHON_EXECUTABLE={sys.executable} && "
            f"{binary_path} {' '.join(sys.argv[1:])}"
        )
        
        # Execute the binary
        os.system(env_command)
        
    except zipfile.BadZipFile:
        print_message("Error: The zip file is corrupted or not a zip file.")
    except Exception as e:
        print_message(f"An error occurred: {e}")
    finally:
        # Clean up temporary directory
        shutil.rmtree(temp_dir)

def main():
    """Main function."""
    print_message("Instagram Creator Tool - Clean Version")
    print_message("This tool appears to be a wrapper for a compiled binary.")
    print_message("The original binary is embedded and will be extracted and executed.")
    print_message("")
    
    # Check if we're running as a standalone script
    if __name__ == '__main__':
        extract_and_run()

if __name__ == '__main__':
    main()