import cv2
import os
import glob
from pyzbar.pyzbar import decode
import numpy as np

def extract_amount_from_khqr(khqr_string):
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

def process_qr_codes(input_dir, output_dir, padding=20):
    if not os.path.exists(output_dir):
        os.makedirs(output_dir)

    image_files = glob.glob(os.path.join(input_dir, '*.[jp][pn]*[g]'))
    
    detector = cv2.QRCodeDetector()
    processed = 0
    failed = 0

    for img_path in image_files:
        filename = os.path.basename(img_path)
        img = cv2.imread(img_path)
        
        if img is None:
            failed += 1
            continue

        qr_data = None
        rect = None
        
        # Try Pyzbar first on original
        decoded_objects = decode(img)
        if decoded_objects:
            qr_data = decoded_objects[0].data.decode('utf-8')
            rect = decoded_objects[0].rect
        
        # If failed, try OpenCV
        points = None
        if not qr_data:
            retval, decoded_info, pts, _ = detector.detectAndDecodeMulti(img)
            if retval and len(decoded_info) > 0 and decoded_info[0]:
                qr_data = decoded_info[0]
                points = pts[0]
        
        # If still failed, try grayscale & threshold with Pyzbar
        if not qr_data:
            gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
            _, thresh = cv2.threshold(gray, 128, 255, cv2.THRESH_BINARY | cv2.THRESH_OTSU)
            decoded_objects = decode(thresh)
            if decoded_objects:
                qr_data = decoded_objects[0].data.decode('utf-8')
                rect = decoded_objects[0].rect

        if not qr_data:
            print(f"Failed: No QR detected in {filename}")
            failed += 1
            continue
            
        amount = extract_amount_from_khqr(qr_data)
        if amount is None:
            print(f"Failed: No Tag 54 in {filename}")
            failed += 1
            continue
            
        new_filename = f"qr_{amount:.2f}.png"
        output_path = os.path.join(output_dir, new_filename)
        
        # Crop logic
        if rect:
            x, y, w, h = rect.left, rect.top, rect.width, rect.height
        elif points is not None:
            x_min, y_min = int(min(points[:, 0])), int(min(points[:, 1]))
            x_max, y_max = int(max(points[:, 0])), int(max(points[:, 1]))
            x, y, w, h = x_min, y_min, x_max - x_min, y_max - y_min
        else:
            # Fallback to saving whole image if no bounds
            x, y, w, h = 0, 0, img.shape[1], img.shape[0]

        size = max(w, h)
        center_x, center_y = x + w // 2, y + h // 2
        
        x_min = max(0, center_x - size // 2 - padding)
        y_min = max(0, center_y - size // 2 - padding)
        x_max = min(img.shape[1], center_x + size // 2 + padding)
        y_max = min(img.shape[0], center_y + size // 2 + padding)
        
        cropped_img = img[y_min:y_max, x_min:x_max]
        
        cv2.imwrite(output_path, cropped_img)
        print(f"Success: {filename} -> {new_filename}")
        processed += 1

    print(f"\nSuccessfully processed: {processed}")
    print(f"Failed: {failed}")

if __name__ == "__main__":
    base_dir = r"d:\xampp\htdocs\Project_Sarana\Hotel_Booking_Ops\public\qr_codes"
    input_dir = os.path.join(base_dir, "MAM_SANORA_PLACEHOLDER_QRCODES", "unedited_qr_filename")
    output_dir = os.path.join(base_dir, "MAM_SANORA_PLACEHOLDER_QRCODES")
    process_qr_codes(input_dir, output_dir)
