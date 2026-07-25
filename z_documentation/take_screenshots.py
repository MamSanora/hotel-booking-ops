#!/usr/bin/env python3
"""
Take screenshots of Dara Meas Hotel Booking System pages
for use in the Capstone Defense Presentation.
Admin credentials: username=admin / password=password (from AdminSeeder.php)
Staff/Reception:   username=reception / password=password (from StaffSeeder.php)
"""
import asyncio
import os
from playwright.async_api import async_playwright

OUTPUT_DIR = "/Users/soursey/Documents/Norton/Year4/Sarana/Hotel_Main/hotel-booking-ops/z_documentation/screenshots"
os.makedirs(OUTPUT_DIR, exist_ok=True)

BASE = "http://127.0.0.1:8000"

async def ss(page, name, wait_ms=2500):
    await page.wait_for_timeout(wait_ms)
    path = os.path.join(OUTPUT_DIR, f"{name}.png")
    await page.screenshot(path=path, full_page=False)
    print(f"  ✔ {name}.png")

async def goto(page, url, wait=2500):
    try:
        await page.goto(BASE + url, wait_until="networkidle", timeout=12000)
    except Exception:
        await page.goto(BASE + url, timeout=8000)
    await page.wait_for_timeout(wait)

async def main():
    print("🏨 Dara Meas Hotel — Capturing System Screenshots\n")
    
    async with async_playwright() as p:
        browser = await p.chromium.launch(headless=True)

        # ── PUBLIC PAGES ──────────────────────────────────────────────────
        ctx_public = await browser.new_context(viewport={"width": 1400, "height": 900})
        pg = await ctx_public.new_page()

        print("📸 Public Pages:")
        await goto(pg, "/")
        await ss(pg, "01_homepage")

        await goto(pg, "/guest/login")
        await ss(pg, "02_guest_login")

        await goto(pg, "/guest/register")
        await ss(pg, "03_guest_register")

        await goto(pg, "/about")
        await ss(pg, "04_about")

        await goto(pg, "/contact")
        await ss(pg, "05_contact")

        await goto(pg, "/gallery")
        await ss(pg, "06_gallery")

        await ctx_public.close()

        # ── ADMIN PANEL ───────────────────────────────────────────────────
        ctx_admin = await browser.new_context(viewport={"width": 1400, "height": 900})
        pga = await ctx_admin.new_page()

        print("\n🔐 Logging in as Admin (admin / password)...")
        await goto(pga, "/admin/login", wait=1500)
        # Fill username field (this login uses username, not email)
        try:
            await pga.fill("input[name='username']", "admin")
        except Exception:
            await pga.fill("input[type='text']", "admin")
        await pga.fill("input[type='password']", "password")
        await pga.click("button[type='submit']")
        await pga.wait_for_timeout(2500)

        if "dashboard" in pga.url or "admin" in pga.url:
            print("  ✔ Admin login successful!")
        else:
            print(f"  ⚠ Current URL: {pga.url}")

        print("\n📸 Admin Panel Pages:")
        await ss(pga, "07_admin_dashboard")

        await goto(pga, "/admin/dashboard/analytics")
        await ss(pga, "08_admin_analytics")

        await goto(pga, "/admin/bookings")
        await ss(pga, "09_admin_bookings")

        await goto(pga, "/admin/rooms")
        await ss(pga, "10_admin_rooms")

        await goto(pga, "/admin/room-types")
        await ss(pga, "11_admin_room_types")

        await goto(pga, "/admin/staff")
        await ss(pga, "12_admin_staff")

        await goto(pga, "/admin/messages")
        await ss(pga, "13_admin_messages")

        await ctx_admin.close()

        # ── RECEPTION PORTAL ──────────────────────────────────────────────
        ctx_rec = await browser.new_context(viewport={"width": 1400, "height": 900})
        pgr = await ctx_rec.new_page()

        # Try staff login page - might be same as admin
        print("\n🔐 Logging in as Receptionist (reception / password)...")
        await goto(pgr, "/admin/login", wait=1500)
        try:
            await pgr.fill("input[name='username']", "reception")
        except Exception:
            await pgr.fill("input[type='text']", "reception")
        await pgr.fill("input[type='password']", "password")
        await pgr.click("button[type='submit']")
        await pgr.wait_for_timeout(2500)

        print(f"  Current URL: {pgr.url}")
        await goto(pgr, "/reception/dashboard")
        await ss(pgr, "14_reception_dashboard", wait_ms=3000)

        await ctx_rec.close()
        await browser.close()

    # Report
    saved = sorted(f for f in os.listdir(OUTPUT_DIR) if f.endswith('.png'))
    print(f"\n✅ Done! {len(saved)} screenshots saved to {OUTPUT_DIR}")
    for f in saved:
        size = os.path.getsize(os.path.join(OUTPUT_DIR, f))
        print(f"   • {f} ({size//1024} KB)")

if __name__ == "__main__":
    asyncio.run(main())
