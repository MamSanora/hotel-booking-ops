import cv2
import os
import glob
from pyzbar.pyzbar import decode

def extract_amount_from_khqr(khqr_string):
    """Parses an EMVCo QR string and extracts Tag 54 (Transaction Amount)."""
    idx = 0
    while idx < len(khqr_string):
        if idx + 4 > len(khqr_string):
            break
        tag = khqr_string[idx:idx+2]
        length_str = khqr_string[idx+2:idx+4]
        try:
            length = int(length_str)
        except ValueError:
            break
        
        value = khqr_string[idx+4:idx+4+length]
        
        if tag == "54":
            return float(value)
            
        idx += 4 + length
    return None

def process_qr_codes(input_dir, output_dir, padding=10):
    if not os.path.exists(output_dir):
        os.makedirs(output_dir)

    image_files = glob.glob(os.path.join(input_dir, '*.[jp][pn]*[g]'))
    
    if not image_files:
        print(f"No images found in {input_dir}")
        return

    processed = 0
    failed = 0

    for img_path in image_files:
        filename = os.path.basename(img_path)
        img = cv2.imread(img_path)
        
        if img is None:
            print(f"Skipping {filename}: Unreadable")
            failed += 1
            continue
            
        # Decode QR using pyzbar (much more reliable than cv2's default detector for static images)
        decoded_objects = decode(img)
        if not decoded_objects:
            print(f"Failed: No QR detected in {filename}")
            failed += 1
            continue
            
        obj = decoded_objects[0]
        qr_data = obj.data.decode('utf-8')
        
        # Extract amount
        amount = extract_amount_from_khqr(qr_data)
        if amount is None:
            print(f"Failed: Could not find Tag 54 (Amount) in {filename}'s QR data")
            failed += 1
            continue
            
        # Format amount to 2 decimal places
        new_filename = f"qr_{amount:.2f}.png"
        output_path = os.path.join(output_dir, new_filename)
        
        # Crop to perfectly square bounding box
        rect = obj.rect
        x, y, w, h = rect.left, rect.top, rect.width, rect.height
        
        # Apply padding and square up
        size = max(w, h)
        center_x, center_y = x + w // 2, y + h // 2
        
        x_min = max(0, center_x - size // 2 - padding)
        y_min = max(0, center_y - size // 2 - padding)
        x_max = min(img.shape[1], center_x + size // 2 + padding)
        y_max = min(img.shape[0], center_y + size // 2 + padding)
        
        cropped_img = img[y_min:y_max, x_min:x_max]
        
        # Save output
        cv2.imwrite(output_path, cropped_img)
        print(f"Success: {filename} -> {new_filename} (Amount: ${amount:.2f})")
        processed += 1

    print(f"\n--- Summary ---")
    print(f"Successfully processed: {processed}")
    print(f"Failed: {failed}")

if __name__ == "__main__":
    base_dir = r"d:\xampp\htdocs\Project_Sarana\Hotel_Booking_Ops\public\qr_codes"
    input_dir = os.path.join(base_dir, "MAM_SANORA_PLACEHOLDER_QRCODES", "unedited_qr_filename")
    output_dir = os.path.join(base_dir, "MAM_SANORA_PLACEHOLDER_QRCODES")
    
    print(f"Input: {input_dir}")
    print(f"Output: {output_dir}")
    
    process_qr_codes(input_dir, output_dir)
