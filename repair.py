with open(r'd:\xampp\htdocs\Project_Sarana\Hotel_Booking_Ops\resources\views\livewire\reception\dashboard.blade.php', 'r', encoding='utf-8') as f:
    lines = f.readlines()

new_lines = []
# 1. Keep lines 1 to 711 (indices 0 to 710)
new_lines.extend(lines[:711])

# 2. Add the missing bridge
bridge = '''            </div>

            {{-- Pending Housekeeping Request (right column) --}}
            @livewire('reception.housekeeping-requests-list')

        </div>{{-- end RIGHT COLUMN --}}

    </div>{{-- end 2-column layout --}}

'''
new_lines.append(bridge)

# 3. Append lines 917 to the end (indices 916 to end)
new_lines.extend(lines[916:])

with open(r'd:\xampp\htdocs\Project_Sarana\Hotel_Booking_Ops\resources\views\livewire\reception\dashboard.blade.php', 'w', encoding='utf-8') as f:
    f.writelines(new_lines)

print('File repaired successfully!')
