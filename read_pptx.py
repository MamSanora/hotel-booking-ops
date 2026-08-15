import zipfile
import xml.etree.ElementTree as ET
import glob
import os

def extract_pptx_text(filepath):
    try:
        text_content = []
        with zipfile.ZipFile(filepath) as z:
            # find all slide xmls
            slide_files = [f for f in z.namelist() if f.startswith('ppt/slides/slide') and f.endswith('.xml')]
            
            for slide_file in slide_files:
                xml_content = z.read(slide_file)
                tree = ET.fromstring(xml_content)
                # namespace for drawingml
                ns = {'a': 'http://schemas.openxmlformats.org/drawingml/2006/main'}
                texts = [node.text for node in tree.findall('.//a:t', namespaces=ns) if node.text]
                if texts:
                    text_content.append(" ".join(texts))
        return "\n".join(text_content)
    except Exception as e:
        return f"Error extracting {filepath}: {e}"

def main():
    directory = "d:/Academic Journey/Norton2/State Preparation/State Exam Questions and Courses/Advanced Database System/Lessons in Class"
    pptx_files = glob.glob(os.path.join(directory, "*.pptx"))
    
    with open("lessons_summary.txt", "w", encoding="utf-8") as out:
        for f in pptx_files:
            out.write(f"\n\n--- FILE: {os.path.basename(f)} ---\n")
            out.write(extract_pptx_text(f))

if __name__ == '__main__':
    main()
