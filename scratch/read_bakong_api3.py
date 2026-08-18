import docx

doc = docx.Document(r"d:\xampp\htdocs\Project_Sarana\Hotel_Booking_Ops\z_documentation\API Bakong.docx")
fullText = []
started = False
for para in doc.paragraphs:
    if "3. Check Transaction Status by MD5" in para.text:
        started = True
    if "4. Check Transaction Status by Full Hash" in para.text:
        break
    if started and para.text.strip():
        fullText.append(para.text.strip())

print('\n'.join(fullText))
