import sys
import subprocess
import os

try:
    from docx import Document
    from docx.shared import Pt, Inches
    from docx.enum.text import WD_ALIGN_PARAGRAPH
except ImportError:
    subprocess.check_call([sys.executable, "-m", "pip", "install", "python-docx"])
    from docx import Document
    from docx.shared import Pt, Inches
    from docx.enum.text import WD_ALIGN_PARAGRAPH

doc = Document()

# Add Title
title = doc.add_heading('System Backup Module Documentation', 0)
title.alignment = WD_ALIGN_PARAGRAPH.CENTER

doc.add_paragraph('Project: Hotel Booking Ops').alignment = WD_ALIGN_PARAGRAPH.CENTER
doc.add_paragraph('Date: July 2026').alignment = WD_ALIGN_PARAGRAPH.CENTER

doc.add_heading('1. Overview', level=1)
doc.add_paragraph(
    "The System Backup Module provides fully automated database and file backups, alongside a manual backup "
    "capability. It is designed with a 'zero-touch' philosophy for non-technical administrators. "
    "Backups are stored locally (with the option to sync to the cloud) and the status of the system's backups is visibly displayed "
    "on the Admin Dashboard."
)

doc.add_heading('2. Installation & Core Dependencies', level=1)
doc.add_paragraph(
    "The core package used to drive the backup functionality is Spatie Laravel Backup. It handles packaging "
    "files and creating database dumps using mysqldump."
)
doc.add_paragraph("Command run: composer require spatie/laravel-backup", style='Intense Quote')
doc.add_paragraph(
    "After downloading, the configuration file was published to config/backup.php."
)

doc.add_heading('3. Configuration', level=1)
doc.add_heading('3.1. Filesystems', level=2)
doc.add_paragraph(
    "A dedicated disk named 'backups' was created in config/filesystems.php pointing to "
    "storage/app/backups. This keeps backup archives neatly segregated from regular application uploads."
)

doc.add_heading('3.2. Backup Package Settings', level=2)
doc.add_paragraph(
    "In config/backup.php, the destination disk was set to 'backups'. Crucially, the "
    "storage/app/backups directory was added to the exclusion list to prevent the backup package from "
    "recursively backing up previous backups, which would cause file sizes to balloon exponentially."
)

doc.add_heading('3.3. Database & MySQLDump', level=2)
doc.add_paragraph(
    "Because the system runs on Windows (XAMPP), the path to the mysqldump binary must be explicitly defined. "
    "A new environment variable DB_DUMP_BINARY_PATH was added to the .env file (using forward slashes to avoid "
    "escaping issues, e.g., D:/xampp/mysql/bin). This is loaded in config/database.php under the MySQL "
    "connection settings."
)

doc.add_heading('4. Admin Dashboard Integration', level=1)
doc.add_heading('4.1. Backup Status Widget', level=2)
doc.add_paragraph(
    "The AdminDashboardController scans the 'backups' disk to find the most recent ZIP archive. It determines "
    "the health of the backup system based on the file's last modified timestamp:"
)
p = doc.add_paragraph()
p.add_run("• Healthy (● Secured): ").bold = True
p.add_run("Backup occurred within the last 25 hours.\n")
p.add_run("• Outdated (⚠ Outdated): ").bold = True
p.add_run("Backup is older than 25 hours.\n")
p.add_run("• No Backup (✕ No Backup): ").bold = True
p.add_run("No backup files found.\n")
p.add_run("• Unknown (? Unknown): ").bold = True
p.add_run("Disk unavailable or misconfigured.")

doc.add_paragraph(
    "The dashboard (resources/views/admin/dashboard.blade.php) displays a colour-coded card summarizing "
    "this status, giving the administrator instant peace of mind."
)

doc.add_heading('4.2. Manual Backup Trigger', level=2)
doc.add_paragraph(
    "A 'Backup Now' button was added to the widget, allowing administrators to trigger a database backup on demand."
)
doc.add_heading('Rate Limiting & Cooldown', level=3)
doc.add_paragraph(
    "To prevent server overload from repeated clicks, a 1-hour cooldown is enforced per administrator. "
    "This logic is housed in the new BackupController. Once triggered, the controller caches the expiry timestamp "
    "and locks out further manual runs for that user. The Blade view detects this cooldown and replaces the active "
    "button with a greyed-out pill showing the remaining minutes."
)

doc.add_heading('5. Challenges Addressed', level=1)
doc.add_paragraph(
    "During development, a 'Call to undefined method getTimeToLive()' error occurred. This happened because "
    "the application uses the database cache driver, which does not support the getTimeToLive() method "
    "value and calculating the remaining time manually, creating a driver-agnostic solution."
)

doc.add_heading('Silent Failure Detection & Winsock Errors', level=2)
doc.add_paragraph(
    "Another issue encountered was the manual backup button reporting 'success' even "
    "when the actual backup generation failed. Initially, we used Artisan::call(), but this ran "
    "inside the web server process, which on Windows can suffer from a degraded Winsock socket context "
    "(Error 10106: WSAEPROVIDERFAILEDINIT). We replaced Artisan::call() with Symfony's Process component "
    "to spawn a completely fresh PHP process. We also explicitly passed the system's TEMP and TMP environment "
    "variables to ensure PHP could create temporary credential files."
)

doc.add_heading('Timezone Display Fix', level=2)
doc.add_paragraph(
    "Finally, the 'Last Backup' timestamp displayed in UTC instead of local time. We fixed this by using "
    "Carbon::createFromTimestampUTC() and explicitly calling setTimezone(config('app.timezone')) "
    "so that the backup time always correctly displays in the hotel's local timezone (e.g., Asia/Phnom_Penh)."
)

doc.add_heading('6. Maintenance Notes', level=1)
p2 = doc.add_paragraph()
p2.add_run("• Dependencies: ").bold = True
p2.add_run("If corrupted dependencies occur during composer operations, delete the vendor directory and run 'composer install'.\n")
p2.add_run("• Scheduled Tasks: ").bold = True
p2.add_run("In production, ensure the Laravel scheduler is running via cron to automate nightly backups (php artisan schedule:run).")

os.makedirs('z_documentation', exist_ok=True)
doc.save('z_documentation/System_Backup_Module_Documentation.docx')
print('Generated z_documentation/System_Backup_Module_Documentation.docx successfully.')
