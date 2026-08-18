import docx

doc = docx.Document(r"d:\xampp\htdocs\Project_Sarana\Hotel_Booking_Ops\z_documentation\API Bakong.docx")
for table in doc.tables:
    for row in table.rows:
        row_data = [cell.text.strip() for cell in row.cells]
        print(" | ".join(row_data))
