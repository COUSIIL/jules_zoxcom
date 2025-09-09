from playwright.sync_api import sync_playwright, Page, expect
import json
import time

def run(playwright):
    browser = playwright.chromium.launch(headless=True)
    context = browser.new_context()
    page = context.new_page()

    base_url = "http://localhost:3000"

    # This is the correct user object structure for the 'auth' key
    dummy_auth_user = {
        "id": 1,
        "username": "testuser",
        "email": "test@example.com",
        "profile_image": "/z.svg",
        "token": "dummy-token-for-testing"
    }

    # Navigate to the base URL to set localStorage
    page.goto(base_url)
    # Use 'auth' as the key, which is what the main app expects
    page.evaluate(f"""
        localStorage.setItem('auth', '{json.dumps(dummy_auth_user)}');
        localStorage.setItem('user', '{json.dumps(dummy_auth_user)}');
    """)

    # Navigate to the diniChat page
    page.goto(f"{base_url}/diniChat")

    # Wait for the main chat layout to be visible
    expect(page.locator(".dini-chat-layout")).to_be_visible(timeout=15000)

    # Retry loop for clicking the "New Chat" button
    for i in range(5):
        try:
            new_chat_button = page.locator("button.new-chat-btn")
            expect(new_chat_button).to_be_visible()
            new_chat_button.click(timeout=5000)
            print("Successfully clicked 'New Chat' button.")
            break
        except Exception as e:
            print(f"Attempt {i+1} to click 'New Chat' failed: {e}")
            if i < 4:
                time.sleep(1)
            else:
                raise

    # Wait for the conversation to be created and marked as active
    expect(page.locator(".conversation-item.active")).to_be_visible(timeout=10000)

    # The input should now be ready
    expect(page.locator(".chat-input-area textarea")).to_be_enabled()

    # Let's type a message and check if it appears
    page.locator(".chat-input-area textarea").fill("Hello, this is a test.")
    expect(page.locator(".chat-input-area textarea")).to_have_value("Hello, this is a test.")

    # Final screenshot of the fully rendered and interactive page
    screenshot_path = "jules-scratch/verification/diniChat_final.png"
    page.screenshot(path=screenshot_path)
    print(f"Screenshot saved to {screenshot_path}")

    browser.close()

with sync_playwright() as playwright:
    run(playwright)
