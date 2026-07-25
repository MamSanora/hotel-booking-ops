import cv2
import os
import glob
import sys

def crop_qr_codes(input_dir, output_dir, padding=20):
    if not os.path.exists(output_dir):
        os.makedirs(output_dir)

    image_files = glob.glob(os.path.join(input_dir, '*.[jp][pn]*[g]')) # matches jpg, png, jpeg
    
    if not image_files:
        print(f"No images found in {input_dir}")
        return

    detector = cv2.QRCodeDetector()
    processed_count = 0

    for img_path in image_files:
        filename = os.path.basename(img_path)
        img = cv2.imread(img_path)
        
        if img is None:
            print(f"Skipping {filename}: Unable to read image")
            continue

        # Detect QR code
        retval, decoded_info, points, straight_qrcode = detector.detectAndDecodeMulti(img)
        
        if retval and len(points) > 0:
            # We assume there's only one QR code per screenshot
            pts = points[0]
            
            # pts is a 4x2 array of coordinates [x, y]
            x_min = int(min(pts[:, 0]))
            x_max = int(max(pts[:, 0]))
            y_min = int(min(pts[:, 1]))
            y_max = int(max(pts[:, 1]))
            
            # Apply padding
            y_min = max(0, y_min - padding)
            y_max = min(img.shape[0], y_max + padding)
            x_min = max(0, x_min - padding)
            x_max = min(img.shape[1], x_max + padding)
            
            # Make it a perfect square based on the longest side to ensure the QR code fits nicely
            width = x_max - x_min
            height = y_max - y_min
            size = max(width, height)
            
            # Re-adjust bounding box to be perfectly square
            center_x = x_min + width // 2
            center_y = y_min + height // 2
            
            x_min_sq = max(0, center_x - size // 2)
            y_min_sq = max(0, center_y - size // 2)
            x_max_sq = min(img.shape[1], x_min_sq + size)
            y_max_sq = min(img.shape[0], y_min_sq + size)

            cropped_img = img[y_min_sq:y_max_sq, x_min_sq:x_max_sq]
            
            output_path = os.path.join(output_dir, filename)
            cv2.imwrite(output_path, cropped_img)
            print(f"Success: Cropped QR from {filename} -> {output_path}")
            processed_count += 1
        else:
            print(f"Failed: No QR code found in {filename}")

    print(f"\nDone! Successfully processed {processed_count}/{len(image_files)} images.")

if __name__ == "__main__":
    # Ensure opencv-python is installed
    try:
        import cv2
    except ImportError:
        print("Please install opencv-python to run this script: pip install opencv-python")
        sys.exit(1)

    print("--- QR Code Auto-Cropper ---")
    input_folder = input("Enter the path to your folder containing the screenshots (e.g., ./raw_screenshots): ").strip()
    output_folder = input("Enter the path to save the cropped QR codes (e.g., ./public/images/qr_codes): ").strip()
    
    crop_qr_codes(input_folder, output_folder)
