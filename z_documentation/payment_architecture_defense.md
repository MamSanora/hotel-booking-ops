# Payment Architecture Defense: Custom EMVCo + Telegram Automation

**Context for Panelists:**
When designing the payment architecture for Darameas Hotel, we evaluated the official ABA PayWay API. While the API is standard, it requires a verified corporate bank account, official business registration documents, and a 2-4 week approval process from the National Bank of Cambodia and ABA. Because Darameas Hotel is currently operating as an independent boutique entity undergoing corporate restructuring, these bureaucratic requirements made the API route unfeasible for our go-to-market timeline.

Instead of abandoning automated payments, we engineered a **highly sophisticated, zero-API payment automation pipeline** that achieves the exact same result using standard banking tools.

## Our Architectural Solution

We built a **Custom EMVCo QR Generator & Telegram Webhook Bridge**.

1. **EMVCo Standard QR Generation (Offline):** 
   Instead of asking an API to generate a QR code, our Laravel backend mathematically generates the raw KHQR/EMVCo string offline. We reverse-engineered the exact Tag 68 (ABA Terminal ID) used by ABA Merchant accounts, allowing us to generate infinite, perfectly valid ABA QR codes locally without any API calls.

2. **Telegram Bot Webhook Bridge (Microservice):**
   When a guest scans the QR code, their ABA app processes the payment and ABA sends a digital receipt to the hotel owner's Telegram Merchant Bot. We built a permanent background daemon (running via PM2) that listens to this Telegram bot 24/7. 

3. **Language-Agnostic Regex Parsing:**
   The daemon reads the incoming receipt in real-time. Because ABA's bot can switch between Khmer and English, we wrote a language-agnostic Regular Expression (Regex) parser that strictly hunts for universal identifiers: the `$` symbol, the 15-digit transaction ID, and the 6-digit APV code.

4. **The "Amount Lock" Concurrency Solution:**
   Because the offline Telegram receipt does not contain the database Booking ID, we faced a potential race condition (Collision) if two guests paid the exact same dollar amount at the exact same millisecond. 
   
   To solve this, we engineered an **Amount Lock** mechanism in our database utilizing `DB::transaction()` and pessimistic locking (`lockForUpdate()`). When Guest A initiates a checkout for $30.00, the system temporarily locks the $30.00 price point for the ABA payment method. If Guest B attempts to checkout with a $30.00 cart, they are politely asked to wait a few minutes. 
   
   This mathematically guarantees that when the Telegram bot reports a $30.00 payment, the Laravel backend can map it to Guest A with 100% certainty, entirely eliminating the race condition.

## Why this is impressive for a Defense Project:
Instead of just using a plug-and-play API, we:
- Implemented **Cryptography/Checksums** (CRC-16/CCITT-FALSE for the EMVCo QR).
- Solved a complex **Concurrency/Race Condition** at the database level using Pessimistic Locking.
- Built a **Microservice** (the Telegram Bridge) that runs as a background daemon and communicates with the main Laravel monolith.
- Mastered **Regular Expressions** for data extraction.

This demonstrates a significantly deeper understanding of software engineering, networking, and database integrity than simply calling a REST API.
