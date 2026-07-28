<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Telegram Bot Token
    |--------------------------------------------------------------------------
    |
    | The token issued by @BotFather on Telegram for your custom bot.
    | Register your bot at https://t.me/BotFather and copy the token here.
    |
    | Format: "<bot_id>:<random_string>"
    | Example: "123456789:AAFxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
    |
    */
    'bot_token' => env('TELEGRAM_BOT_TOKEN', ''),

    /*
    |--------------------------------------------------------------------------
    | Webhook Secret Token
    |--------------------------------------------------------------------------
    |
    | An optional secret string you set when registering the webhook with
    | Telegram. Telegram will include it in every webhook request as the
    | "X-Telegram-Bot-Api-Secret-Token" header, letting us verify the
    | request genuinely came from Telegram and not a third party.
    |
    | Generate any random string (e.g. openssl rand -hex 32).
    |
    */
    'webhook_secret' => env('TELEGRAM_WEBHOOK_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | ABA Account Number
    |--------------------------------------------------------------------------
    |
    | The ABA account number guests should transfer to.
    | Displayed on the payment instruction page.
    |
    */
    'aba_account_number' => env('TELEGRAM_ABA_ACCOUNT_NUMBER', ''),

    /*
    |--------------------------------------------------------------------------
    | ABA Merchant Name (for KHQR Generation)
    |--------------------------------------------------------------------------
    |
    | The merchant name registered with ABA — exactly as it appears on your
    | ABA merchant account. This is embedded in the KHQR payload and shown
    | to the payer in their banking app when they scan the QR code.
    |
    | Decoded from your existing qr_30.00.png: 'KEO SAMNANG DARAMEAS'
    |
    */
    'aba_merchant_name' => env('TELEGRAM_ABA_MERCHANT_NAME', 'KEO SAMNANG DARAMEAS'),

    'aba_account_number_2' => env('TELEGRAM_ABA_ACCOUNT_NUMBER_2', ''),
    'aba_merchant_name_2' => env('TELEGRAM_ABA_MERCHANT_NAME_2', 'MAM SANORA DARA MEAS'),

    /*
    |--------------------------------------------------------------------------
    | Telegram Group / Chat ID
    |--------------------------------------------------------------------------
    |
    | The numeric Chat ID of the private Telegram group where your bot lives
    | and where ABA bank notifications are forwarded.
    | The webhook controller only accepts updates from this chat.
    |
    | To find your Chat ID: add @userinfobot to the group and send /start.
    |
    */
    'group_chat_id' => env('TELEGRAM_GROUP_CHAT_ID', ''),
];
