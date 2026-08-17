import os
import glob
import re

files = glob.glob('app/**/*.php', recursive=True) + glob.glob('routes/**/*.php', recursive=True)

for filepath in files:
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    new_content = content.replace("'room.roomType'", "'bookingRooms.roomType'")
    new_content = new_content.replace('"room.roomType"', '"bookingRooms.roomType"')
    
    # We should also check for with('room') or with(['room', ...])
    # But ONLY within context of Booking::with, $booking->load, etc.
    # To be safe, let's just use regex for array items:
    # 'room' or "room" inside with([ ... ]) or load([ ... ])
    new_content = re.sub(r"with\(\s*\[(.*?)'room'(.*?)\]\s*\)", r"with([\g<1>'bookingRooms.room'\g<2>])", new_content, flags=re.DOTALL)
    new_content = re.sub(r"load\(\s*\[(.*?)'room'(.*?)\]\s*\)", r"load([\g<1>'bookingRooms.room'\g<2>])", new_content, flags=re.DOTALL)
    
    # Check for ->whereHas('room', 
    new_content = new_content.replace("whereHas('room',", "whereHas('bookingRooms.room',")
    new_content = new_content.replace('whereHas("room",', 'whereHas("bookingRooms.room",')

    # Wait, earlier I found 'room.roomType' in a line like:
    #             'room.roomType',
    # That was inside an array spreading over multiple lines.
    # The simple replace "'room.roomType'" handles it perfectly.
    
    if new_content != content:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(new_content)
        print(f'Updated {filepath}')

