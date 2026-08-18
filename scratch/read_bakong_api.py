import docx

doc = docx.Document(r"d:\xampp\htdocs\Project_Sarana\Hotel_Booking_Ops\z_documentation\API Bakong.docx")
fullText = []
for para in doc.paragraphs:
    fullText.append(para.text)

print('\n'.join(fullText))
